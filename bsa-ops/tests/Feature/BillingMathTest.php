<?php

namespace Tests\Feature;

use App\Models\Discount;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class BillingMathTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
    }

    public function test_tax_inclusive_invoice_back_computes_vat_and_totals_add_up(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym); // 3,500 + 1,000 admission, VAT-inclusive
        $member = $this->makeMember();

        $invoice = app(SubscriptionService::class)->subscribe($member, $plan)->invoice()->with('items')->first();

        // Gross stays what was charged.
        $this->assertSame(450000, $invoice->total);
        $this->assertSame(450000, $invoice->balance);

        // VAT back-computed: gross - gross*10000/11300 per line.
        $expectedTax = (350000 - intdiv(350000 * 10000, 11300))
            + (100000 - intdiv(100000 * 10000, 11300));
        $this->assertSame($expectedTax, $invoice->tax_total);

        // Line totals reconcile with header totals.
        $this->assertSame($invoice->total, (int) $invoice->items->sum('line_total'));
        $this->assertSame($invoice->tax_total, (int) $invoice->items->sum('tax_amount'));
    }

    public function test_tax_exclusive_invoice_adds_vat_on_top(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym, [
            'code' => 'GYM-EX',
            'price_includes_tax' => false,
            'admission_fee' => 0,
        ]);
        $member = $this->makeMember();

        $invoice = app(SubscriptionService::class)->subscribe($member, $plan)->invoice()->first();

        $expectedTax = (int) round(350000 * 1300 / 10000);
        $this->assertSame(350000 + $expectedTax, $invoice->total);
        $this->assertSame($expectedTax, $invoice->tax_total);
    }

    public function test_non_taxable_plan_has_zero_vat(): void
    {
        $pool = $this->makeDepartment('POOL', 'Pool');
        $plan = $this->makePackPlan($pool);
        $member = $this->makeMember();

        $invoice = app(SubscriptionService::class)->subscribe($member, $plan)->invoice()->first();

        $this->assertSame(0, $invoice->tax_total);
        $this->assertSame(350000, $invoice->total);
    }

    public function test_percent_discount_applies_pre_tax_on_plan_line_only(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym, ['admission_fee' => 0]);
        $member = $this->makeMember();
        $discount = Discount::create([
            'code' => 'TEN', 'name' => '10%', 'type' => 'percent', 'value' => 1000, 'is_active' => true,
        ]);

        $sub = app(SubscriptionService::class)->subscribe($member, $plan, discount: $discount);
        $invoice = $sub->invoice()->first();

        $this->assertSame(35000, $sub->discount_amount); // 10% of 3,500
        $this->assertSame(315000, $invoice->total); // inclusive: discounted gross
        $this->assertSame(1, $discount->fresh()->used_count);
    }

    public function test_fixed_discount_is_capped_at_line_amount(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym, ['code' => 'GYM-CHEAP', 'price' => 40000, 'admission_fee' => 0]);
        $member = $this->makeMember();
        $discount = Discount::create([
            'code' => 'BIG', 'name' => 'Huge', 'type' => 'fixed', 'value' => 100000, 'is_active' => true,
        ]);

        $sub = app(SubscriptionService::class)->subscribe($member, $plan, discount: $discount);

        $this->assertSame(40000, $sub->discount_amount);
        $this->assertSame(0, $sub->invoice()->first()->total);
    }

    public function test_admission_fee_charged_only_on_first_subscription(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        $service = app(SubscriptionService::class);

        $first = $service->subscribe($member, $plan);
        $second = $service->renew($first);

        $this->assertSame(100000, $first->admission_fee);
        $this->assertSame(0, $second->admission_fee);
        $this->assertSame(350000, $second->invoice()->first()->total);
    }
}
