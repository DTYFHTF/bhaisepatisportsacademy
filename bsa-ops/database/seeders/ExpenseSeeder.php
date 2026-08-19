<?php

namespace Database\Seeders;

use App\Enums\ExpenseStatus;
use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Services\NumberSequenceService;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20260722);

        $sequences = app(NumberSequenceService::class);
        $categories = ExpenseCategory::pluck('id', 'code');
        $departments = Department::pluck('id', 'code');
        $accountant = User::where('email', 'accounts@bsa.com')->first();
        $manager = User::where('email', 'manager@bsa.com')->first();

        // [category, department|null(=overhead), description, base amount NPR, jitter %]
        // Scaled to the 60-member seed cohort so demo P&L margins read sensibly.
        $monthly = [
            ['RENT', null, 'Premises rent (share)', 22000, 0],
            ['SALARY', null, 'Support staff wages', 38000, 5],
            ['UTILITY', null, 'Electricity (NEA)', 9000, 25],
            ['UTILITY', null, 'Water & internet', 3000, 10],
            ['SUPPLY', 'POOL', 'Pool chlorine & chemicals', 7000, 20],
            ['MAINT', 'POOL', 'Pool filtration servicing', 2500, 40],
            ['MAINT', 'GYM', 'Gym equipment maintenance', 4500, 50],
            ['SUPPLY', 'SAUNA', 'Sauna consumables & towels', 2000, 30],
            ['SUPPLY', 'BADMINTON', 'Shuttlecocks & nets', 2500, 35],
            ['MAINT', 'FUTSAL', 'Turf upkeep', 2000, 40],
            ['MARKETING', null, 'Social media boosts', 2000, 60],
        ];

        for ($monthsAgo = 8; $monthsAgo >= 0; $monthsAgo--) {
            $monthStart = today()->subMonthsNoOverflow($monthsAgo)->startOfMonth();

            foreach ($monthly as [$cat, $dept, $desc, $baseNpr, $jitter]) {
                $day = $monthStart->copy()->addDays(mt_rand(1, min(25, today()->day)));

                if ($day->gt(today())) {
                    continue;
                }

                $amount = (int) round($baseNpr * 100 * (1 + (mt_rand(-$jitter, $jitter) / 100)));
                $approved = $monthsAgo > 0 || mt_rand(0, 1) === 1;

                Expense::create([
                    'voucher_number' => $sequences->voucherNumber(),
                    'expense_category_id' => $categories[$cat],
                    'department_id' => $dept ? $departments[$dept] : null,
                    'description' => $desc,
                    'amount' => $amount,
                    'expense_date' => $day,
                    'payment_method' => mt_rand(0, 9) < 7 ? 'bank_transfer' : 'cash',
                    'vendor_name' => collect(['Lalitpur Suppliers', 'Himal Traders', 'Everest Enterprises', 'Patan Hardware', null])->random(),
                    'reference_no' => 'BILL-' . mt_rand(1000, 99999),
                    'status' => $approved ? ExpenseStatus::Approved : ExpenseStatus::Recorded,
                    'recorded_by' => $accountant?->id,
                    'approved_by' => $approved ? $manager?->id : null,
                    'approved_at' => $approved ? $day->copy()->addDays(2) : null,
                ]);
            }
        }

        $this->command->info('Expenses: ' . Expense::count());
    }
}
