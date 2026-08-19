<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'RENT', 'name' => 'Rent & Lease'],
            ['code' => 'SALARY', 'name' => 'Salaries & Wages'],
            ['code' => 'UTILITY', 'name' => 'Utilities'],
            ['code' => 'MAINT', 'name' => 'Maintenance & Repairs'],
            ['code' => 'SUPPLY', 'name' => 'Supplies & Consumables'],
            ['code' => 'EQUIP', 'name' => 'Equipment'],
            ['code' => 'MARKETING', 'name' => 'Marketing'],
            ['code' => 'MISC', 'name' => 'Miscellaneous'],
        ];

        foreach ($categories as $i => $category) {
            ExpenseCategory::updateOrCreate(
                ['code' => $category['code']],
                [...$category, 'sort_order' => $i + 1, 'is_active' => true],
            );
        }
    }
}
