<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Department::pluck('id', 'code');

        // Prices in paisa. member_price ≈ 15% under walk-in on kitchen items —
        // "open to all, specially for Club users".
        $products = [
            // ---- Consumables (department costs, sold occasionally) ----
            ['sku' => 'SHUT-YON-PC', 'name' => 'Yonex Mavis 350 shuttlecock', 'category' => 'consumable', 'dept' => 'BADMINTON', 'unit' => 'piece', 'cost_price' => 18000, 'price' => 25000, 'reorder_level' => 24],
            ['sku' => 'SHUT-YON-TUBE', 'name' => 'Yonex Mavis 350 (tube of 6)', 'category' => 'consumable', 'dept' => 'BADMINTON', 'unit' => 'tube', 'cost_price' => 100000, 'price' => 140000, 'reorder_level' => 4],
            ['sku' => 'GRIP-TAPE', 'name' => 'Racket grip tape', 'category' => 'consumable', 'dept' => 'BADMINTON', 'unit' => 'piece', 'cost_price' => 8000, 'price' => 15000, 'member_price' => 12000, 'reorder_level' => 10],
            ['sku' => 'POOL-CHLOR', 'name' => 'Chlorine granules (5kg bucket)', 'category' => 'consumable', 'dept' => 'POOL', 'unit' => 'bucket', 'cost_price' => 350000, 'price' => 450000, 'reorder_level' => 2],
            ['sku' => 'FUTSAL-BALL', 'name' => 'Futsal match ball', 'category' => 'consumable', 'dept' => 'FUTSAL', 'unit' => 'piece', 'cost_price' => 180000, 'price' => 250000, 'reorder_level' => 3],

            // ---- Pro shop ----
            ['sku' => 'SHOP-TSHIRT', 'name' => 'BSA training t-shirt', 'category' => 'shop', 'dept' => 'SHOP', 'unit' => 'piece', 'cost_price' => 45000, 'price' => 85000, 'member_price' => 70000, 'reorder_level' => 10],
            ['sku' => 'SHOP-SHAKER', 'name' => 'Protein shaker bottle', 'category' => 'shop', 'dept' => 'SHOP', 'unit' => 'piece', 'cost_price' => 25000, 'price' => 45000, 'member_price' => 38000, 'reorder_level' => 6],
            ['sku' => 'SHOP-TOWEL', 'name' => 'Gym towel', 'category' => 'shop', 'dept' => 'SHOP', 'unit' => 'piece', 'cost_price' => 20000, 'price' => 40000, 'member_price' => 32000, 'reorder_level' => 8],
            ['sku' => 'SHOP-LOCK', 'name' => 'Locker padlock', 'category' => 'shop', 'dept' => 'SHOP', 'unit' => 'piece', 'cost_price' => 15000, 'price' => 30000, 'member_price' => 25000, 'reorder_level' => 5],
            ['sku' => 'SHOP-CAP', 'name' => 'Swimming cap', 'category' => 'shop', 'dept' => 'SHOP', 'unit' => 'piece', 'cost_price' => 12000, 'price' => 25000, 'member_price' => 20000, 'reorder_level' => 8],
            ['sku' => 'SHOP-GOGGLE', 'name' => 'Swimming goggles', 'category' => 'shop', 'dept' => 'SHOP', 'unit' => 'piece', 'cost_price' => 40000, 'price' => 75000, 'member_price' => 65000, 'reorder_level' => 5],
            ['sku' => 'SHOP-PBAR', 'name' => 'Protein bar', 'category' => 'shop', 'dept' => 'SHOP', 'unit' => 'piece', 'cost_price' => 12000, 'price' => 22000, 'member_price' => 18000, 'reorder_level' => 20],
            ['sku' => 'SHOP-EDRINK', 'name' => 'Energy drink can', 'category' => 'shop', 'dept' => 'SHOP', 'unit' => 'can', 'cost_price' => 10000, 'price' => 18000, 'member_price' => 15000, 'reorder_level' => 24],
            ['sku' => 'SHOP-WATER', 'name' => 'Mineral water 1L', 'category' => 'shop', 'dept' => 'SHOP', 'unit' => 'bottle', 'cost_price' => 2000, 'price' => 5000, 'member_price' => 4000, 'reorder_level' => 48],

            // ---- Kitchen: made to order (no stock tracking), VAT-exempt food ----
            ['sku' => 'KIT-TEA', 'name' => 'Milk tea', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'cup', 'cost_price' => 1500, 'price' => 4000, 'member_price' => 3000, 'track' => false, 'taxable' => false],
            ['sku' => 'KIT-COFFEE', 'name' => 'Black coffee', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'cup', 'cost_price' => 2500, 'price' => 8000, 'member_price' => 6500, 'track' => false, 'taxable' => false],
            ['sku' => 'KIT-JUICE', 'name' => 'Fresh seasonal juice', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'glass', 'cost_price' => 6000, 'price' => 15000, 'member_price' => 12500, 'track' => false, 'taxable' => false],
            ['sku' => 'KIT-MOMO', 'name' => 'Steam momo (chicken)', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'plate', 'cost_price' => 9000, 'price' => 18000, 'member_price' => 15000, 'track' => false, 'taxable' => false],
            ['sku' => 'KIT-CHOWMEIN', 'name' => 'Chicken chowmein', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'plate', 'cost_price' => 8000, 'price' => 16000, 'member_price' => 13500, 'track' => false, 'taxable' => false],
            ['sku' => 'KIT-FRICE', 'name' => 'Chicken fried rice', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'plate', 'cost_price' => 9000, 'price' => 17000, 'member_price' => 14500, 'track' => false, 'taxable' => false],
            ['sku' => 'KIT-EGGS', 'name' => 'Boiled eggs (2)', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'plate', 'cost_price' => 4000, 'price' => 8000, 'member_price' => 6500, 'track' => false, 'taxable' => false],
            ['sku' => 'KIT-SHAKE', 'name' => 'Banana protein shake', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'glass', 'cost_price' => 12000, 'price' => 25000, 'member_price' => 20000, 'track' => false, 'taxable' => false],
            ['sku' => 'KIT-SANDWICH', 'name' => 'Grilled chicken sandwich', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'piece', 'cost_price' => 11000, 'price' => 22000, 'member_price' => 18500, 'track' => false, 'taxable' => false],
            ['sku' => 'KIT-SALAD', 'name' => 'Fitness salad bowl', 'category' => 'kitchen', 'dept' => 'KITCHEN', 'unit' => 'bowl', 'cost_price' => 10000, 'price' => 20000, 'member_price' => 17000, 'track' => false, 'taxable' => false],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'category' => $product['category'],
                    'department_id' => $dept[$product['dept']] ?? null,
                    'unit' => $product['unit'],
                    'cost_price' => $product['cost_price'],
                    'price' => $product['price'],
                    'member_price' => $product['member_price'] ?? null,
                    'is_taxable' => $product['taxable'] ?? true,
                    'price_includes_tax' => true,
                    'track_stock' => $product['track'] ?? true,
                    'reorder_level' => $product['reorder_level'] ?? 0,
                    'is_active' => true,
                ],
            );
        }
    }
}
