<?php

namespace App\Services;

use App\Enums\InvoiceSource;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Point of sale — kitchen and pro shop. Every sale is an Invoice in the
 * one money ledger (source=pos): walk-ins pay instantly with no member;
 * Club members get member pricing and may put the sale on their account.
 */
class PosService
{
    /** Sentinel payment method meaning "charge to the member's account". */
    public const ON_ACCOUNT = 'account';

    public function __construct(
        private readonly BillingService $billing,
        private readonly InventoryService $inventory,
        private readonly NumberSequenceService $sequences,
    ) {
    }

    /**
     * @param  array<int, array{product: Product, quantity: int}>  $lines
     * @param  PaymentMethod|string  $method  a PaymentMethod, or self::ON_ACCOUNT
     */
    public function sale(
        array $lines,
        ?Member $member,
        PaymentMethod|string $method,
        ?User $cashier = null,
    ): Invoice {
        if ($lines === []) {
            throw ValidationException::withMessages(['lines' => 'The cart is empty.']);
        }

        $onAccount = $method === self::ON_ACCOUNT;

        if ($onAccount && ! $member) {
            throw ValidationException::withMessages(['member' => 'On-account sales need a member attached.']);
        }

        if (! $onAccount && ! $method instanceof PaymentMethod) {
            $method = PaymentMethod::from($method);
        }

        return DB::transaction(function () use ($lines, $member, $method, $cashier, $onAccount) {
            $graceDays = (int) \App\Models\Setting::get('dues_grace_days', 7);

            $invoice = Invoice::create([
                'invoice_number' => $this->sequences->invoiceNumber(),
                'member_id' => $member?->id,
                'source' => InvoiceSource::Pos,
                'issue_date' => today(),
                'due_date' => $onAccount ? today()->addDays($graceDays) : today(),
                'subtotal' => 0,
                'total' => 0,
                'balance' => 0,
                'status' => InvoiceStatus::Issued,
                'created_by' => $cashier?->id,
            ]);

            $sort = 0;

            foreach ($lines as $line) {
                $product = $line['product'];
                $quantity = (int) $line['quantity'];

                if ($quantity < 1) {
                    throw ValidationException::withMessages(['quantity' => 'Quantities must be at least 1.']);
                }

                if (! $product->is_active) {
                    throw ValidationException::withMessages(['product' => "{$product->name} is not for sale."]);
                }

                $this->billing->addLine(
                    invoice: $invoice,
                    description: $product->name . ($quantity > 1 ? " × {$quantity}" : ''),
                    unitPrice: $product->priceFor($member),
                    discount: 0,
                    taxable: $product->is_taxable,
                    priceIncludesTax: $product->price_includes_tax,
                    itemable: $product,
                    sort: $sort++,
                    quantity: $quantity,
                );

                // Guarded decrement — throws (rolling everything back) on shortfall.
                $this->inventory->sellStock($product, $quantity, $invoice, $cashier);
            }

            $invoice->load('items');
            $invoice->recalculateTotals();
            $invoice->save();

            if (! $onAccount && $invoice->total > 0) {
                $this->billing->applyPayment($invoice, [
                    'amount' => $invoice->total,
                    'method' => $method,
                ], receiver: $cashier, instant: true);
            }

            return $invoice->refresh()->load('items', 'payments');
        });
    }
}
