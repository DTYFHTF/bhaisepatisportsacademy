<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            ExpenseCategorySeeder::class,
            MembershipPlanSeeder::class,
            DiscountSeeder::class,
            MemberSeeder::class,
            SubscriptionAndBillingSeeder::class,
            CheckInSeeder::class,
            ExpenseSeeder::class,
            AccessDeviceSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            PurchaseSeeder::class,
            PosSaleSeeder::class,
            ConsumptionSeeder::class,
        ]);
    }
}
