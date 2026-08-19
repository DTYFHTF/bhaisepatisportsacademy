<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'GYM', 'name' => 'Gym & Fitness', 'cost_center_code' => 'CC-GYM', 'capacity' => 60, 'opens_at' => '05:00', 'closes_at' => '21:00', 'color' => 'success', 'monthly_budget' => 15000000],
            ['code' => 'POOL', 'name' => 'Swimming Pool', 'cost_center_code' => 'CC-POOL', 'capacity' => 40, 'opens_at' => '06:00', 'closes_at' => '19:00', 'color' => 'info', 'monthly_budget' => 20000000],
            ['code' => 'SAUNA', 'name' => 'Sauna & Steam', 'cost_center_code' => 'CC-SAUNA', 'capacity' => 12, 'opens_at' => '07:00', 'closes_at' => '20:00', 'color' => 'warning', 'monthly_budget' => 6000000],
            ['code' => 'BADMINTON', 'name' => 'Badminton Courts', 'cost_center_code' => 'CC-BADM', 'capacity' => 24, 'opens_at' => '05:30', 'closes_at' => '21:00', 'color' => 'danger', 'monthly_budget' => 8000000],
            ['code' => 'FUTSAL', 'name' => 'Futsal Ground', 'cost_center_code' => 'CC-FUTSAL', 'capacity' => 20, 'opens_at' => '06:00', 'closes_at' => '21:00', 'color' => 'gray', 'monthly_budget' => 10000000],
            // Pure cost/revenue centers — no door gating, open to everyone.
            ['code' => 'KITCHEN', 'name' => 'Club Kitchen', 'cost_center_code' => 'CC-KITCHEN', 'opens_at' => '07:00', 'closes_at' => '20:00', 'color' => 'warning', 'monthly_budget' => 5000000, 'is_access_controlled' => false],
            ['code' => 'SHOP', 'name' => 'Pro Shop', 'cost_center_code' => 'CC-SHOP', 'opens_at' => '08:00', 'closes_at' => '19:00', 'color' => 'info', 'monthly_budget' => 3000000, 'is_access_controlled' => false],
        ];

        foreach ($departments as $i => $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                [...$dept, 'sort_order' => $i + 1, 'is_active' => true],
            );
        }
    }
}
