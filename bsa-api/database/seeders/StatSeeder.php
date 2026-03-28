<?php

namespace Database\Seeders;

use App\Models\SiteStat;
use Illuminate\Database\Seeder;

class StatSeeder extends Seeder
{
    public function run(): void
    {
        SiteStat::truncate();

        $stats = [
            ['value_label' => '200+', 'label' => 'Active Members',  'sort_order' => 1],
            ['value_label' => '5+',   'label' => 'Expert Coaches',  'sort_order' => 2],
            ['value_label' => '3',    'label' => 'Badminton Courts', 'sort_order' => 3],
            ['value_label' => '6',    'label' => 'Years Experience', 'sort_order' => 4],
        ];

        SiteStat::insert(array_map(fn($s) => array_merge($s, [
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]), $stats));
    }
}
