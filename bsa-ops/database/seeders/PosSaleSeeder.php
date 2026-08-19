<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Models\Member;
use App\Models\Product;
use App\Models\User;
use App\Services\PosService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * ~90 days of kitchen and shop sales, run through PosService so pricing,
 * invoices, payments, and the stock ledger are all real.
 */
class PosSaleSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20260725);

        $pos = app(PosService::class);
        $desk = User::where('email', 'desk1@bsa.com')->first();
        $members = Member::where('status', 'active')->get();

        $kitchen = Product::active()->where('category', 'kitchen')->get();
        $shop = Product::active()->where('category', 'shop')->get();

        $methodPool = collect([
            ...array_fill(0, 70, PaymentMethod::Cash),
            ...array_fill(0, 12, PaymentMethod::Esewa),
            ...array_fill(0, 8, PaymentMethod::Khalti),
            ...array_fill(0, 10, PaymentMethod::Card),
        ]);

        $realToday = Carbon::today(); // anchor BEFORE any setTestNow
        $sales = 0;
        $failures = 0;

        for ($daysAgo = 90; $daysAgo >= 0; $daysAgo--) {
            $day = $realToday->copy()->subDays($daysAgo);
            $dailySales = mt_rand(3, 7);

            for ($s = 0; $s < $dailySales; $s++) {
                Carbon::setTestNow($day->copy()->setTime(mt_rand(7, 19), mt_rand(0, 59)));

                // 60% kitchen orders, 40% shop baskets.
                $pool = mt_rand(0, 99) < 60 ? $kitchen : $shop;
                $lines = [];

                foreach ($pool->random(mt_rand(1, 3)) as $product) {
                    $lines[] = ['product' => $product->fresh(), 'quantity' => mt_rand(1, 2)];
                }

                // Half the sales have a member attached; of those, 20% go on account.
                $member = mt_rand(0, 1) === 1 && $members->isNotEmpty() ? $members->random() : null;
                $method = ($member && mt_rand(0, 99) < 20)
                    ? PosService::ON_ACCOUNT
                    : $methodPool[mt_rand(0, $methodPool->count() - 1)];

                try {
                    $pos->sale($lines, $member, $method, $desk);
                    $sales++;
                } catch (\Throwable) {
                    $failures++; // stock ran dry — realistic, skip
                }
            }
        }

        Carbon::setTestNow();

        $this->command->info("POS sales: {$sales} (skipped {$failures} out-of-stock)");
    }
}
