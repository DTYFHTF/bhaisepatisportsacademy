<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Stock arrivals over the last 8 months, run through InventoryService so
 * the ledger, vouchers, and stock cache stay consistent.
 */
class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20260724);

        $inventory = app(InventoryService::class);
        $accountant = User::where('email', 'accounts@bsa.com')->first();
        $products = Product::where('track_stock', true)->get()->keyBy('sku');
        $suppliers = Supplier::pluck('id', 'name');

        $sportsSupplier = Supplier::find($suppliers['Himalayan Sports Distributors']);
        $beverage = Supplier::find($suppliers['Everest Beverage Wholesale']);
        $hardware = Supplier::find($suppliers['Patan Hardware & General']);

        $realToday = Carbon::today(); // anchor before any setTestNow

        // Monthly shuttlecock + consumable restock.
        for ($monthsAgo = 8; $monthsAgo >= 0; $monthsAgo--) {
            $date = $realToday->copy()->subMonthsNoOverflow($monthsAgo)->addDays(mt_rand(2, 8));

            if ($date->gt($realToday)) {
                continue;
            }

            Carbon::setTestNow($date->copy()->setTime(11, 0));

            $inventory->receivePurchase($sportsSupplier, array_values(array_filter([
                ['product' => $products['SHUT-YON-PC'], 'quantity' => mt_rand(48, 96), 'unit_cost' => 17500],
                ['product' => $products['SHUT-YON-TUBE'], 'quantity' => mt_rand(6, 12), 'unit_cost' => 98000],
                mt_rand(0, 1) ? ['product' => $products['GRIP-TAPE'], 'quantity' => mt_rand(10, 30), 'unit_cost' => 7500] : null,
            ])), $date, 'HSD-' . mt_rand(1000, 9999), $accountant);

            $inventory->receivePurchase($hardware, [
                ['product' => $products['POOL-CHLOR'], 'quantity' => mt_rand(2, 4), 'unit_cost' => 340000],
            ], $date, null, $accountant);

            $inventory->receivePurchase($beverage, array_values(array_filter([
                ['product' => $products['SHOP-WATER'], 'quantity' => mt_rand(48, 120), 'unit_cost' => 1800],
                ['product' => $products['SHOP-EDRINK'], 'quantity' => mt_rand(24, 48), 'unit_cost' => 9500],
                ['product' => $products['SHOP-PBAR'], 'quantity' => mt_rand(20, 40), 'unit_cost' => 11500],
            ])), $date, 'EBW-' . mt_rand(1000, 9999), $accountant);
        }

        // Merchandise intakes: opening stock plus a mid-period top-up so the
        // shop doesn't run dry over 90 days of sales.
        foreach ([6 => 'HSD-OPENING', 2 => 'HSD-RESTOCK'] as $monthsAgo => $ref) {
            $date = $realToday->copy()->subMonthsNoOverflow($monthsAgo);
            Carbon::setTestNow($date->copy()->setTime(14, 0));

            $inventory->receivePurchase($sportsSupplier, [
                ['product' => $products['SHOP-TSHIRT'], 'quantity' => 60, 'unit_cost' => 42000],
                ['product' => $products['SHOP-SHAKER'], 'quantity' => 40, 'unit_cost' => 24000],
                ['product' => $products['SHOP-TOWEL'], 'quantity' => 50, 'unit_cost' => 19000],
                ['product' => $products['SHOP-LOCK'], 'quantity' => 35, 'unit_cost' => 14000],
                ['product' => $products['SHOP-CAP'], 'quantity' => 40, 'unit_cost' => 11000],
                ['product' => $products['SHOP-GOGGLE'], 'quantity' => 30, 'unit_cost' => 38000],
                ['product' => $products['FUTSAL-BALL'], 'quantity' => 8, 'unit_cost' => 175000],
            ], $date, $ref, $accountant, $ref === 'HSD-OPENING' ? 'Opening merchandise stock' : 'Merchandise top-up');
        }

        Carbon::setTestNow();

        $this->command->info('Purchases: ' . \App\Models\Purchase::count()
            . ' | Stock movements: ' . \App\Models\StockMovement::count());
    }
}
