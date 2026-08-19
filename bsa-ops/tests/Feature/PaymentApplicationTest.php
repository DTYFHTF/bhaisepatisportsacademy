<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\StaffRole;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class PaymentApplicationTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    private BillingService $billing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
        $this->billing = app(BillingService::class);
    }

    private function invoice(): Invoice
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym, ['admission_fee' => 0]); // total 3,500 inclusive
        $member = $this->makeMember();

        return app(SubscriptionService::class)->subscribe($member, $plan)->invoice()->first();
    }

    public function test_partial_then_full_cash_payment_transitions_status(): void
    {
        $invoice = $this->invoice();

        $this->billing->applyPayment($invoice, ['amount' => 100000, 'method' => 'cash']);
        $invoice->refresh();
        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->status);
        $this->assertSame(250000, $invoice->balance);

        $this->billing->applyPayment($invoice, ['amount' => 250000, 'method' => 'cash']);
        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertSame(0, $invoice->balance);
        $this->assertSame(350000, $invoice->paid_total);
    }

    public function test_overpayment_is_rejected(): void
    {
        $invoice = $this->invoice();

        $this->expectException(ValidationException::class);
        $this->billing->applyPayment($invoice, ['amount' => 999999, 'method' => 'cash']);
    }

    public function test_cheque_enters_pending_and_only_counts_after_verification(): void
    {
        $invoice = $this->invoice();
        $accountant = $this->makeStaff(StaffRole::Accountant);

        $payment = $this->billing->applyPayment($invoice, [
            'amount' => 350000, 'method' => 'cheque', 'cheque_number' => '123456',
        ]);

        $this->assertSame(PaymentStatus::PendingVerification, $payment->status);
        $this->assertSame(0, $invoice->fresh()->paid_total);

        $this->billing->verifyPayment($payment, $accountant);

        $this->assertSame(PaymentStatus::Completed, $payment->fresh()->status);
        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);
    }

    public function test_bouncing_a_completed_payment_reopens_the_invoice(): void
    {
        $invoice = $this->invoice();
        $accountant = $this->makeStaff(StaffRole::Accountant);

        $payment = $this->billing->applyPayment($invoice, ['amount' => 350000, 'method' => 'cash']);
        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);

        $this->billing->bouncePayment($payment, $accountant);

        $invoice->refresh();
        $this->assertSame(350000, $invoice->balance);
        $this->assertSame(0, $invoice->paid_total);
        $this->assertSame(PaymentStatus::Bounced, $payment->fresh()->status);
    }

    public function test_refund_reopens_balance_and_marks_payment_refunded_when_full(): void
    {
        $invoice = $this->invoice();
        $accountant = $this->makeStaff(StaffRole::Accountant);
        $payment = $this->billing->applyPayment($invoice, ['amount' => 350000, 'method' => 'cash']);

        $this->billing->refundPayment($payment, 350000, 'Member relocated', $accountant);

        $this->assertSame(PaymentStatus::Refunded, $payment->fresh()->status);
        $this->assertSame(350000, $invoice->fresh()->balance);
    }

    public function test_refund_cannot_exceed_refundable_amount(): void
    {
        $invoice = $this->invoice();
        $accountant = $this->makeStaff(StaffRole::Accountant);
        $payment = $this->billing->applyPayment($invoice, ['amount' => 100000, 'method' => 'cash']);

        $this->expectException(ValidationException::class);
        $this->billing->refundPayment($payment, 200000, 'Too much', $accountant);
    }

    public function test_paid_invoice_cannot_be_voided(): void
    {
        $invoice = $this->invoice();
        $manager = $this->makeStaff(StaffRole::Manager);
        $this->billing->applyPayment($invoice, ['amount' => 350000, 'method' => 'cash']);

        $this->expectException(ValidationException::class);
        $this->billing->voidInvoice($invoice->fresh(), $manager, 'Mistake');
    }

    public function test_unpaid_invoice_voids_cleanly(): void
    {
        $invoice = $this->invoice();
        $manager = $this->makeStaff(StaffRole::Manager);

        $this->billing->voidInvoice($invoice, $manager, 'Duplicate entry');

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Void, $invoice->status);
        $this->assertSame(0, $invoice->balance);
        $this->assertNotNull($invoice->voided_at);
    }

    public function test_payments_on_void_invoices_are_rejected(): void
    {
        $invoice = $this->invoice();
        $manager = $this->makeStaff(StaffRole::Manager);
        $this->billing->voidInvoice($invoice, $manager, 'Void first');

        $this->expectException(ValidationException::class);
        $this->billing->applyPayment($invoice->fresh(), ['amount' => 100, 'method' => 'cash']);
    }
}
