<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $discounts = [
            ['code' => 'STUDENT10', 'name' => 'Student discount', 'type' => 'percent', 'value' => 1000], // 10%
            ['code' => 'CORP15', 'name' => 'Corporate tie-up', 'type' => 'percent', 'value' => 1500], // 15%
            ['code' => 'FAMILY500', 'name' => 'Family member flat', 'type' => 'fixed', 'value' => 50000], // NPR 500
            ['code' => 'NEWYEAR2082', 'name' => 'New Year 2082 offer', 'type' => 'percent', 'value' => 2000, 'valid_until' => '2026-08-31', 'max_uses' => 50],
        ];

        foreach ($discounts as $discount) {
            Discount::updateOrCreate(
                ['code' => $discount['code']],
                [...$discount, 'is_active' => true],
            );
        }
    }
}
