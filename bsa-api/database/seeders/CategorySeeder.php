<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Skincare',  'value' => 'SKINCARE',  'description' => 'Cleansers, serums, and moisturizers.', 'sort_order' => 1],
            ['name' => 'Aftercare', 'value' => 'AFTERCARE', 'description' => 'Post-wax sprays, scrubs, and masks.',  'sort_order' => 2],
            ['name' => 'Wax Kits',  'value' => 'WAX_KIT',   'description' => 'At-home waxing kits and tools.',       'sort_order' => 3],
        ];

        foreach ($categories as $data) {
            ProductCategory::updateOrCreate(
                ['value' => $data['value']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
