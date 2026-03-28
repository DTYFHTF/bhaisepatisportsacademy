<?php

namespace Database\Seeders;

use App\Models\KitchenItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KitchenSeeder extends Seeder
{
    public function run(): void
    {
        KitchenItem::truncate();

        $items = [
            // Pre-workout
            ['slug' => 'banana-dates',       'name' => 'Banana & Dates Pack',       'description' => 'Natural energy boost with 2 ripe bananas and a handful of Medjool dates',            'price' => 15000, 'category' => 'pre-workout',  'is_popular' => true,  'sort_order' => 1],
            ['slug' => 'oats-bowl',          'name' => 'Overnight Oats Bowl',       'description' => 'Rolled oats soaked overnight with honey, chia seeds, and seasonal fruit',             'price' => 25000, 'category' => 'pre-workout',  'is_popular' => false, 'sort_order' => 2],
            ['slug' => 'granola-bar',        'name' => 'Homemade Granola Bar',       'description' => 'Oats, nuts, and honey pressed into a dense energy bar',                               'price' => 12000, 'category' => 'pre-workout',  'is_popular' => false, 'sort_order' => 3],

            // Post-workout
            ['slug' => 'protein-wrap',       'name' => 'Chicken Protein Wrap',      'description' => 'Grilled chicken, egg whites, veggies, and yogurt sauce in a wholewheat wrap',         'price' => 35000, 'category' => 'post-workout', 'is_popular' => true,  'sort_order' => 4],
            ['slug' => 'dal-bhat',           'name' => 'Recovery Dal Bhat',         'description' => 'Classic Nepali dal bhat with lentils, rice, vegetables, and achaar',                  'price' => 30000, 'category' => 'post-workout', 'is_popular' => false, 'sort_order' => 5],
            ['slug' => 'egg-platter',        'name' => 'Boiled Egg Platter',        'description' => '4 boiled eggs with a sprinkle of black salt, chilli flakes, and crackers',             'price' => 20000, 'category' => 'post-workout', 'is_popular' => false, 'sort_order' => 6],

            // Snacks
            ['slug' => 'peanut-butter-toast','name' => 'Peanut Butter Toast',       'description' => 'Whole-grain toast with natural peanut butter and banana slices',                       'price' => 18000, 'category' => 'snacks',       'is_popular' => false, 'sort_order' => 7],
            ['slug' => 'fruit-platter',      'name' => 'Seasonal Fruit Platter',    'description' => 'Mixed seasonal fruits from Nepal, freshly cut and served with chilli-salt',            'price' => 22000, 'category' => 'snacks',       'is_popular' => true,  'sort_order' => 8],
            ['slug' => 'chana-chaat',        'name' => 'Spiced Chickpea Chaat',     'description' => 'Boiled chickpeas with onion, tomato, green chilli, and lemon juice',                   'price' => 15000, 'category' => 'snacks',       'is_popular' => false, 'sort_order' => 9],

            // Drinks
            ['slug' => 'protein-shake',      'name' => 'Protein Shake',             'description' => 'Whey protein blended with milk, banana, and peanut butter',                            'price' => 28000, 'category' => 'drinks',       'is_popular' => true,  'sort_order' => 10],
            ['slug' => 'green-smoothie',     'name' => 'Green Recovery Smoothie',   'description' => 'Spinach, cucumber, ginger, lemon, and apple blended fresh',                            'price' => 25000, 'category' => 'drinks',       'is_popular' => false, 'sort_order' => 11],
            ['slug' => 'lemon-electrolyte',  'name' => 'Lemon Electrolyte Water',   'description' => 'Chilled lemon water with Himalayan salt, honey, and tulsi',                            'price' => 10000, 'category' => 'drinks',       'is_popular' => false, 'sort_order' => 12],
            ['slug' => 'masala-tea',         'name' => 'Masala Chiya',              'description' => 'Traditional Nepali spiced milk tea, brewed to order',                                   'price' =>  8000, 'category' => 'drinks',       'is_popular' => false, 'sort_order' => 13],
        ];

        foreach ($items as $item) {
            KitchenItem::create(array_merge(['id' => Str::uuid()], $item));
        }
    }
}
