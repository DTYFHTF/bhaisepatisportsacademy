<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\MemberSubscription;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * All money math lives here. Integer paisa throughout; rounding happens
 * once per line, never on running totals.
 */
class BillingService
{
    public function __construct(
        private readonly NumberSequenceService $sequences,
    ) {
    }

    // ---------------------------------------------------------------
    // Invoice creation
    // ---------------------------------------------------------------

    public function createInvoiceForSubscription(
        MemberSubscription $subscription,
        bool $includeAdmission = false,
        ?User $creator = null,
    ): Invoice {
        return DB::transaction(function () use ($subscription, $includeAdmission, $creator) {
            $plan = $subscription->plan;
            $graceDays = (int) Setting::get('dues_grace_days', 7);

            $invoice = Invoice::create([
                'invoice_number' => $this->sequences->invoiceNumber(),
                'member_id' => $subscription->member_id,
                'member_subscription_id' => $subscription->id,
                'issue_date' => today(),
                'due_date' => today()->addDays($graceDays),
                'subtotal' => 0,
                'total' => 0,
                'balance' => 0,
                'status' => InvoiceStatus::Issued,
                'created_by' => $creator?->id,
            ]);

            $sort = 0;

            $this->addLine(
                invoice: $invoice,
                description: "{$plan->name} ({$subscription->starts_on->format('j M Y')}"
                    . ($subscription->ends_on ? " – {$subscription->ends_on->format('j M Y')})" : ')'),
                unitPrice: $subscription->price,
                discount: $subscription->discount_amount,
                taxable: $plan->is_taxable,
                priceIncludesTax: $plan->price_includes_tax,
                itemable: $plan,
                sort: $sort++,
            );

            if ($includeAdmission && $subscription->admission_fee > 0) {
                $this->addLine(
                    invoice: $invoice,
                    description: 'Admission fee',
                    unitPrice: $subscription->admission_fee,
                    discount: 0,
                    taxable: $plan->is_taxable,
                    priceIncludesTax: $plan->price_includes_tax,
                    itemable: $plan,
                    sort: $sort++,
                );
            }

            $invoice->load('items');
            $invoice->recalculateTotals();
            $invoice->save();

            return $invoice;
        });
    }

    /**
     * Tax math per line:
     *  - inclusive: gross stays as charged; VAT back-computed out of it.
     *  - exclusive: VAT added on top of the discounted base.
     * Rates handled in basis points to stay in integer arithmetic.
     *
     * Public so PosService can build product invoices with the same math.
     */
    public function addLine(
        Invoice $invoice,
        string $description,
        int $unitPrice,
        int $discount,
        bool $taxable,
        bool $priceIncludesTax,
        ?Model $itemable,
        int $sort,
        int $quantity = 1,
    ): void {
        $ratePercent = $taxable ? (float) Setting::get('tax_rate_percent', 13) : 0.0;
        $rateBp = (int) round($ratePercent * 100);

        $base = max(0, $unitPrice * $quantity - $discount);

        if ($rateBp === 0) {
            $tax = 0;
            $lineTotal = $base;
        } elseif ($priceIncludesTax) {
            $net = intdiv($base * 10000, 10000 + $rateBp);
            $tax = $base - $net;
            $lineTotal = $base;
        } else {
            $tax = (int) round($base * $rateBp / 10000);
            $lineTotal = $base + $tax;
        }

        $invoice->items()->create([
            'description' => $description,
            'itemable_type' => $itemable ? $itemable::class : null,
            'itemable_id' => $itemable?->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discount,
            'tax_rate' => $ratePercent,
            'tax_amount' => $tax,
            'line_total' => $lineTotal,
            'sort_order' => $sort,
        ]);
    }

    // ---------------------------------------------------------------
    // Payments
    // ---------------------------------------------------------------

    /**
     * Record a payment against an invoice.
     *
     * Cash and card are trusted immediately; cheque and gateway methods
     * enter as pending_verification and only count toward paid_total
     * once verified. POS passes $instant=true — the customer shows the
     * wallet confirmation at the counter, so nothing is left pending.
     */
    public function applyPayment(Invoice $invoice, array $data, ?User $receiver = null, bool $instant = false): Payment
    {
        if ($invoice->status === InvoiceStatus::Void) {
            throw ValidationException::withMessages(['invoice' => 'Cannot pay a void invoice.']);
        }

        $amount = (int) $data['amount'];
        $method = $data['method'] instanceof PaymentMethod ? $data['method'] : PaymentMethod::from($data['method']);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Payment amount must be positive.']);
        }

        if ($amount > $invoice->balance) {
            throw ValidationException::withMessages([
                'amount' => 'Payment exceeds the outstanding balance (NPR ' . number_format($invoice->balance / 100, 2) . ').',
            ]);
        }

        $instantMethods = [PaymentMethod::Cash, PaymentMethod::Card];
        $status = ($instant || in_array($method, $instantMethods, true))
            ? PaymentStatus::Completed
            : PaymentStatus::PendingVerification;

        return DB::transaction(function () use ($invoice, $data, $amount, $method, $status, $receiver) {
            $payment = Payment::create([
                'receipt_number' => $this->sequences->receiptNumber(),
                'invoice_id' => $invoice->id,
                'member_id' => $invoice->member_id,
                'amount' => $amount,
                'method' => $method,
                'status' => $status,
                'gateway_txn_id' => $data['gateway_txn_id'] ?? null,
                'gateway_payload' => $data['gateway_payload'] ?? null,
                'cheque_number' => $data['cheque_number'] ?? null,
                'cheque_bank' => $data['cheque_bank'] ?? null,
                'cheque_date' => $data['cheque_date'] ?? null,
                'received_by' => $receiver?->id,
                'received_at' => $data['received_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            if ($status === PaymentStatus::Completed) {
                $this->settle($invoice, $amount);
            }

            return $payment;
        });
    }

    public function verifyPayment(Payment $payment, User $verifier): Payment
    {
        if ($payment->status !== PaymentStatus::PendingVerification) {
            throw ValidationException::withMessages(['payment' => 'Only pending payments can be verified.']);
        }

        return DB::transaction(function () use ($payment, $verifier) {
            $payment->update([
                'status' => PaymentStatus::Completed,
                'verified_at' => now(),
                'verified_by' => $verifier->id,
            ]);

            $this->settle($payment->invoice, $payment->amount);

            return $payment;
        });
    }

    public function bouncePayment(Payment $payment, User $actor): Payment
    {
        return DB::transaction(function () use ($payment, $actor) {
            $wasCompleted = $payment->status === PaymentStatus::Completed;

            $payment->update([
                'status' => PaymentStatus::Bounced,
                'verified_at' => now(),
                'verified_by' => $actor->id,
            ]);

            if ($wasCompleted) {
                $this->settle($payment->invoice, -$payment->amount);
            }

            return $payment;
        });
    }

    public function refundPayment(Payment $payment, int $amount, string $reason, User $actor, ?PaymentMethod $method = null): PaymentRefund
    {
        if ($payment->status !== PaymentStatus::Completed) {
            throw ValidationException::withMessages(['payment' => 'Only completed payments can be refunded.']);
        }

        $alreadyRefunded = (int) $payment->refunds()->sum('amount');

        if ($amount <= 0 || $amount > $payment->amount - $alreadyRefunded) {
            throw ValidationException::withMessages(['amount' => 'Refund exceeds the refundable amount.']);
        }

        return DB::transaction(function () use ($payment, $amount, $reason, $actor, $method, $alreadyRefunded) {
            $refund = PaymentRefund::create([
                'payment_id' => $payment->id,
                'amount' => $amount,
                'method' => $method ?? $payment->method,
                'reason' => $reason,
                'refunded_by' => $actor->id,
                'refunded_at' => now(),
            ]);

            if ($alreadyRefunded + $amount >= $payment->amount) {
                $payment->update(['status' => PaymentStatus::Refunded]);
            }

            $this->settle($payment->invoice, -$amount);

            return $refund;
        });
    }

    public function voidInvoice(Invoice $invoice, User $actor, string $reason): Invoice
    {
        if ($invoice->paid_total > 0) {
            throw ValidationException::withMessages(['invoice' => 'Refund its payments before voiding this invoice.']);
        }

        $invoice->update([
            'status' => InvoiceStatus::Void,
            'balance' => 0,
            'voided_at' => now(),
            'voided_by' => $actor->id,
            'void_reason' => $reason,
        ]);

        return $invoice;
    }

    /**
     * Apply a signed amount to the invoice's paid_total and derive status.
     */
    private function settle(Invoice $invoice, int $delta): void
    {
        $invoice->refresh();

        $paid = max(0, $invoice->paid_total + $delta);
        $balance = max(0, $invoice->total - $paid);

        $status = match (true) {
            $balance === 0 && $paid > 0 => InvoiceStatus::Paid,
            $paid > 0 => InvoiceStatus::PartiallyPaid,
            $invoice->due_date->isPast() => InvoiceStatus::Overdue,
            default => InvoiceStatus::Issued,
        };

        $invoice->update([
            'paid_total' => $paid,
            'balance' => $balance,
            'status' => $status,
        ]);
    }
}
