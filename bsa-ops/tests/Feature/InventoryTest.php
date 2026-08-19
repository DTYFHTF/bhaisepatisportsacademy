<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    private InventoryService $inventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
        $this->inventory = app(InventoryService::class);
    }

    private function shuttlecock(): Product
    {
        return Product::create([
            'sku' => 'SHUT-01', 'name' => 'Shuttlecock', 'category' => 'consumable',
            'unit' => 'piece', 'cost_price' => 18000, 'price' => 25000, 'reorder_level' => 10,
        ]);
    }

    public function test_purchase_receipt_raises_stock_and_writes_ledger(): void
    {
        $product = $this->shuttlecock();
        $supplier = Supplier::create(['name' => 'Yonex Nepal']);

        $purchase = $this->inventory->receivePurchase($supplier, [
            ['product' => $product, 'quantity' => 24, 'unit_cost' => 17500],
        ]);

        $this->assertStringStartsWith('PUR-2082-83-', $purchase->voucher_number);
        $this->assertSame(24 * 17500, $purchase->total);
        $this->assertSame(24, $product->fresh()->stock_on_hand);
        $this->assertSame(17500, $product->fresh()->cost_price); // replacement cost updated

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 24,
            'type' => StockMovementType::Purchase->value,
            'reference_type' => \App\Models\Purchase::class,
        ]);
    }

    public function test_single_shuttlecock_consumption_is_tracked_to_a_department(): void
    {
        $badminton = $this->makeDepartment('BADMINTON', 'Badminton');
        $product = $this->shuttlecock();
        $this->inventory->receivePurchase(null, [
            ['product' => $product, 'quantity' => 6, 'unit_cost' => 18000],
        ]);

        $movement = $this->inventory->consume($product->fresh(), 1, $badminton, notes: 'Court 2');

        $this->assertSame(5, $product->fresh()->stock_on_hand);
        $this->assertSame(-1, $movement->quantity);
        $this->assertSame($badminton->id, $movement->department_id);
        $this->assertSame(18000, $movement->unit_cost);
    }

    public function test_consumption_refuses_shortfall_and_stock_is_untouched(): void
    {
        $product = $this->shuttlecock();
        $this->inventory->receivePurchase(null, [
            ['product' => $product, 'quantity' => 2, 'unit_cost' => 18000],
        ]);

        try {
            $this->inventory->consume($product->fresh(), 5);
            $this->fail('Expected shortfall rejection');
        } catch (ValidationException) {
        }

        $this->assertSame(2, $product->fresh()->stock_on_hand);
        $this->assertSame(0, StockMovement::where('type', StockMovementType::Consumption)->count());
    }

    public function test_adjustment_reconciles_cache_and_ledger(): void
    {
        $product = $this->shuttlecock();
        $this->inventory->receivePurchase(null, [
            ['product' => $product, 'quantity' => 20, 'unit_cost' => 18000],
        ]);

        $this->inventory->adjust($product->fresh(), 17, reason: 'Two damaged, one lost');

        $product->refresh();
        $this->assertSame(17, $product->stock_on_hand);
        $this->assertSame(17, (int) StockMovement::where('product_id', $product->id)->sum('quantity'));

        // Counting the same number again is a no-op.
        $this->assertNull($this->inventory->adjust($product, 17));
    }

    public function test_cache_always_equals_ledger_sum_through_mixed_operations(): void
    {
        $dept = $this->makeDepartment('BADMINTON', 'Badminton');
        $product = $this->shuttlecock();

        $this->inventory->receivePurchase(null, [['product' => $product, 'quantity' => 50, 'unit_cost' => 18000]]);
        $this->inventory->consume($product->fresh(), 3, $dept);
        $this->inventory->adjust($product->fresh(), 45);
        $this->inventory->receivePurchase(null, [['product' => $product->fresh(), 'quantity' => 12, 'unit_cost' => 17000]]);
        $this->inventory->consume($product->fresh(), 1, $dept);

        $this->assertSame(
            (int) StockMovement::where('product_id', $product->id)->sum('quantity'),
            $product->fresh()->stock_on_hand,
        );
        $this->assertSame(56, $product->fresh()->stock_on_hand);
    }
}
