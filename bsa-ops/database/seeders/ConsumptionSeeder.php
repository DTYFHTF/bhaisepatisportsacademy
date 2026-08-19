<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Internal stock issues — the "every single shuttlecock tracked" showcase.
 * Consumption is valued at cost and lands on the department's P&L.
 */
class ConsumptionSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20260726);

        $inventory = app(InventoryService::class);
        $desk = User::where('email', 'desk1@bsa.com')->first();
        $badminton = Department::where('code', 'BADMINTON')->first();
        $pool = Department::where('code', 'POOL')->first();
        $shuttle = Product::where('sku', 'SHUT-YON-PC')->first();
        $chlorine = Product::where('sku', 'POOL-CHLOR')->first();

        $realToday = Carbon::today(); // anchor before setTestNow

        // Shuttlecocks to the courts a few times a week.
        for ($daysAgo = 84; $daysAgo >= 0; $daysAgo -= mt_rand(2, 4)) {
            Carbon::setTestNow($realToday->copy()->subDays($daysAgo)->setTime(mt_rand(6, 18), mt_rand(0, 59)));

            try {
                $inventory->consume(
                    $shuttle->fresh(), mt_rand(2, 6), $badminton, $desk,
                    collect(['Court 1 session', 'Court 2 session', 'Evening league', 'Coaching batch'])->random(),
                );
            } catch (\Throwable) {
                // dry — skip
            }
        }

        // Chlorine to the pool weekly.
        for ($daysAgo = 84; $daysAgo >= 0; $daysAgo -= 7) {
            Carbon::setTestNow($realToday->copy()->subDays($daysAgo)->setTime(7, 30));

            try {
                $inventory->consume($chlorine->fresh(), 1, $pool, $desk, 'Weekly pool treatment');
            } catch (\Throwable) {
                // dry — skip
            }
        }

        Carbon::setTestNow();

        $this->command->info('Consumption movements: '
            . \App\Models\StockMovement::where('type', 'consumption')->count());
    }
}
