<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        ProductCategory::query()->delete();

        $categories = [
            ['name' => 'Equipment',  'value' => 'EQUIPMENT',  'description' => 'Rackets, shuttlecocks, court shoes, and gear.', 'sort_order' => 1],
            ['name' => 'Apparel',    'value' => 'APPAREL',    'description' => 'Sports clothing, jerseys, and activewear.',      'sort_order' => 2],
            ['name' => 'Nutrition',  'value' => 'NUTRITION',  'description' => 'Protein, supplements, and sports drinks.',       'sort_order' => 3],
        ];

        foreach ($categories as $data) {
            ProductCategory::updateOrCreate(
                ['value' => $data['value']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
