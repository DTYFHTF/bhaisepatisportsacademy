<?php

namespace Tests\Feature;

use App\Enums\InvoiceSource;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class PosServiceTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    private PosService $pos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
        $this->pos = app(PosService::class);
    }

    private function momo(): Product
    {
        // Kitchen dish: made to order, VAT-exempt, member price 150 vs 180.
        return Product::create([
            'sku' => 'KIT-MOMO', 'name' => 'Steam momo', 'category' => 'kitchen',
            'unit' => 'plate', 'price' => 18000, 'member_price' => 15000,
            'track_stock' => false, 'is_taxable' => false,
        ]);
    }

    private function stockedShaker(int $qty = 10): Product
    {
        $product = Product::create([
            'sku' => 'SHOP-SHAKER', 'name' => 'Shaker bottle', 'category' => 'shop',
            'unit' => 'piece', 'cost_price' => 24000, 'price' => 45000, 'member_price' => 38000,
        ]);

        app(InventoryService::class)->receivePurchase(null, [
            ['product' => $product, 'quantity' => $qty, 'unit_cost' => 24000],
        ]);

        return $product->fresh();
    }

    public function test_walk_in_cash_sale_creates_paid_pos_invoice_with_no_member(): void
    {
        $invoice = $this->pos->sale([['product' => $this->momo(), 'quantity' => 2]], null, 'cash');

        $this->assertSame(InvoiceSource::Pos, $invoice->source);
        $this->assertNull($invoice->member_id);
        $this->assertSame(36000, $invoice->total); // 2 × walk-in 180
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertSame(PaymentStatus::Completed, $invoice->payments->first()->status);
    }

    public function test_active_member_gets_member_price(): void
    {
        $member = $this->makeMember();

        $invoice = $this->pos->sale([['product' => $this->momo(), 'quantity' => 2]], $member, 'cash');

        $this->assertSame(30000, $invoice->total); // 2 × member 150
        $this->assertSame($member->id, $invoice->member_id);
    }

    public function test_lapsed_member_pays_walk_in_price(): void
    {
        $member = $this->makeMember(['status' => 'expired']);

        $invoice = $this->pos->sale([['product' => $this->momo(), 'quantity' => 1]], $member, 'cash');

        $this->assertSame(18000, $invoice->total);
    }

    public function test_pos_wallet_payment_is_instantly_completed(): void
    {
        $invoice = $this->pos->sale([['product' => $this->momo(), 'quantity' => 1]], null, 'esewa');

        $this->assertSame(PaymentStatus::Completed, $invoice->payments->first()->status);
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
    }

    public function test_on_account_sale_leaves_invoice_outstanding_and_feeds_member_dues(): void
    {
        $member = $this->makeMember();

        $invoice = $this->pos->sale([['product' => $this->momo(), 'quantity' => 2]], $member, PosService::ON_ACCOUNT);

        $this->assertSame(30000, $invoice->balance);
        $this->assertSame(InvoiceStatus::Issued, $invoice->status);
        $this->assertCount(0, $invoice->payments);
        $this->assertSame(30000, $member->outstandingBalance());
    }

    public function test_on_account_requires_a_member(): void
    {
        $this->expectException(ValidationException::class);
        $this->pos->sale([['product' => $this->momo(), 'quantity' => 1]], null, PosService::ON_ACCOUNT);
    }

    public function test_tracked_product_sale_decrements_stock_and_writes_ledger(): void
    {
        $shaker = $this->stockedShaker(10);

        $invoice = $this->pos->sale([['product' => $shaker, 'quantity' => 3]], null, 'cash');

        $this->assertSame(7, $shaker->fresh()->stock_on_hand);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $shaker->id,
            'quantity' => -3,
            'type' => 'sale',
            'reference_type' => \App\Models\Invoice::class,
            'reference_id' => $invoice->id,
        ]);
    }

    public function test_insufficient_stock_rejects_the_whole_sale_atomically(): void
    {
        $shaker = $this->stockedShaker(2);
        $momo = $this->momo();

        try {
            $this->pos->sale([
                ['product' => $momo, 'quantity' => 1],
                ['product' => $shaker, 'quantity' => 5],
            ], null, 'cash');
            $this->fail('Expected stock rejection');
        } catch (ValidationException) {
        }

        // Everything rolled back: no invoice, no payment, stock untouched.
        $this->assertSame(0, \App\Models\Invoice::count());
        $this->assertSame(2, $shaker->fresh()->stock_on_hand);
    }

    public function test_kitchen_dishes_skip_stock_tracking(): void
    {
        $this->pos->sale([['product' => $this->momo(), 'quantity' => 4]], null, 'cash');

        $this->assertSame(0, \App\Models\StockMovement::count());
    }

    public function test_vat_applies_to_taxable_shop_items(): void
    {
        $shaker = $this->stockedShaker();

        $invoice = $this->pos->sale([['product' => $shaker, 'quantity' => 1]], null, 'cash');

        // Inclusive VAT back-computed out of the 450 gross.
        $expectedTax = 45000 - intdiv(45000 * 10000, 11300);
        $this->assertSame(45000, $invoice->total);
        $this->assertSame($expectedTax, $invoice->tax_total);
    }
}
