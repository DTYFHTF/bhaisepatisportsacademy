<?php

namespace Database\Seeders;

use App\Enums\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ── EQUIPMENT ───────────────────────────────────────────────────
            [
                'slug'             => 'yonex-nanoflare-racket',
                'name'             => 'Yonex Nanoflare 370 Speed Racket',
                'tagline'          => 'Lightweight speed on court',
                'price'            => 850000,
                'compare_at_price' => 1050000,
                'category'         => Category::EQUIPMENT,
                'description'      => 'A lightweight yet powerful badminton racket designed for fast swings and precise control. Ideal for intermediate to advanced players.',
                'ingredients'      => '',
                'tags'             => ['equipment', 'bestseller', 'rackets'],
                'variants'         => [['4U-G5', 850000, 12], ['3U-G5', 900000, 8]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1613918431703-aa50889e3be4?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Yonex Nanoflare 370 Racket'],
                ],
            ],
            [
                'slug'             => 'feather-shuttlecocks-12pk',
                'name'             => 'Feather Shuttlecocks (12-Pack)',
                'tagline'          => 'Tournament-grade flight',
                'price'            => 180000,
                'compare_at_price' => null,
                'category'         => Category::EQUIPMENT,
                'description'      => 'Premium goose feather shuttlecocks with cork base. Consistent flight and durability for competitive play and training.',
                'ingredients'      => '',
                'tags'             => ['equipment', 'shuttles', 'essential'],
                'variants'         => [['Speed 77', 180000, 40], ['Speed 78', 180000, 30]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Feather Shuttlecocks Pack'],
                ],
            ],
            [
                'slug'             => 'court-shoes-grip-pro',
                'name'             => 'Court Shoes Grip Pro',
                'tagline'          => 'Superior court grip',
                'price'            => 650000,
                'compare_at_price' => 800000,
                'category'         => Category::EQUIPMENT,
                'description'      => 'Non-marking rubber sole court shoes with cushioned midsole. Designed for quick lateral movements on indoor courts.',
                'ingredients'      => '',
                'tags'             => ['equipment', 'shoes', 'new-arrival'],
                'variants'         => [['EU 40', 650000, 6], ['EU 42', 650000, 8], ['EU 44', 650000, 5]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Court Shoes Grip Pro'],
                ],
            ],
            [
                'slug'             => 'racket-grip-tape-3pk',
                'name'             => 'Overgrip Tape (3-Pack)',
                'tagline'          => 'Better grip, better game',
                'price'            => 35000,
                'compare_at_price' => null,
                'category'         => Category::EQUIPMENT,
                'description'      => 'Sweat-absorbing PU overgrip tape for badminton rackets. Provides a tacky, comfortable hold during intense rallies.',
                'ingredients'      => '',
                'tags'             => ['equipment', 'accessories', 'essential'],
                'variants'         => [['White', 35000, 50], ['Black', 35000, 45]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Overgrip Tape Pack'],
                ],
            ],

            // ── APPAREL ─────────────────────────────────────────────────────
            [
                'slug'             => 'bsa-training-tee',
                'name'             => 'BSA Training Tee',
                'tagline'          => 'Official academy gear',
                'price'            => 120000,
                'compare_at_price' => null,
                'category'         => Category::APPAREL,
                'description'      => 'Breathable, moisture-wicking training t-shirt with the BSA logo. Lightweight polyester fabric perfect for court sessions.',
                'ingredients'      => '',
                'tags'             => ['apparel', 'bestseller', 'new-arrival'],
                'variants'         => [['S', 120000, 15], ['M', 120000, 25], ['L', 120000, 20], ['XL', 120000, 10]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'BSA Training Tee'],
                ],
            ],
            [
                'slug'             => 'bsa-shorts-pro',
                'name'             => 'BSA Court Shorts',
                'tagline'          => 'Move without limits',
                'price'            => 95000,
                'compare_at_price' => null,
                'category'         => Category::APPAREL,
                'description'      => 'Lightweight badminton shorts with elastic waistband and side pockets. Designed for freedom of movement on court.',
                'ingredients'      => '',
                'tags'             => ['apparel', 'essential'],
                'variants'         => [['S', 95000, 12], ['M', 95000, 20], ['L', 95000, 18], ['XL', 95000, 8]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'BSA Court Shorts'],
                ],
            ],

            // ── NUTRITION ───────────────────────────────────────────────────
            [
                'slug'             => 'whey-protein-1kg',
                'name'             => 'Whey Protein Powder (1kg)',
                'tagline'          => 'Fuel your recovery',
                'price'            => 450000,
                'compare_at_price' => 550000,
                'category'         => Category::NUTRITION,
                'description'      => 'Premium whey protein powder for post-workout muscle recovery. 24g protein per serving with low sugar. Chocolate flavour.',
                'ingredients'      => 'Whey Protein Concentrate, Cocoa Powder, Natural Sweetener, Lecithin',
                'tags'             => ['nutrition', 'bestseller', 'recovery'],
                'variants'         => [['Chocolate', 450000, 20], ['Vanilla', 450000, 15]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1593095948071-474c5cc2c4d8?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Whey Protein Powder'],
                ],
            ],
            [
                'slug'             => 'electrolyte-sachets-20pk',
                'name'             => 'Electrolyte Sachets (20-Pack)',
                'tagline'          => 'Stay hydrated, play longer',
                'price'            => 85000,
                'compare_at_price' => null,
                'category'         => Category::NUTRITION,
                'description'      => 'Rapid hydration electrolyte sachets with sodium, potassium, and magnesium. Mix with water during or after training.',
                'ingredients'      => 'Sodium Citrate, Potassium Chloride, Magnesium Glycinate, Natural Lemon Flavour, Stevia',
                'tags'             => ['nutrition', 'essential', 'hydration'],
                'variants'         => [['Lemon', 85000, 35], ['Orange', 85000, 25]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1622543925917-763c34d1a86e?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Electrolyte Sachets'],
                ],
            ],
            [
                'slug'             => 'energy-bar-box-12',
                'name'             => 'Energy Bar Box (12 bars)',
                'tagline'          => 'Quick fuel, great taste',
                'price'            => 240000,
                'compare_at_price' => 290000,
                'category'         => Category::NUTRITION,
                'description'      => 'Oat-based energy bars with dates, nuts, and honey. Perfect pre-workout fuel or post-game snack. No artificial ingredients.',
                'ingredients'      => 'Rolled Oats, Dates, Almonds, Honey, Peanut Butter, Dark Chocolate Chips, Salt',
                'tags'             => ['nutrition', 'new-arrival', 'snacks'],
                'variants'         => [['Mixed Box', 240000, 18]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1622484212850-eb596d769edc?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Energy Bar Box'],
                ],
            ],
        ];

        // Clear existing data so the seeder is safely re-runnable.
        ProductImage::query()->delete();
        ProductVariant::query()->delete();
        DB::table('product_pairs')->delete();
        Product::truncate();

        foreach ($products as $data) {
            $variants = $data['variants'];
            $images   = $data['images'];
            unset($data['variants'], $data['images']);

            $product = Product::create($data);

            foreach ($variants as [$label, $priceOverride, $stock]) {
                $skuSlug = strtoupper(substr(str_replace('-', '', $product->slug), 0, 8));
                $skuLabel = strtoupper(str_replace([' ', 'ml', 'g'], '', $label));
                $product->variants()->create([
                    'label'          => $label,
                    'sku'            => 'BSA-' . $skuSlug . '-' . $skuLabel,
                    'price_override' => $priceOverride,
                    'stock'          => $stock,
                ]);
            }

            foreach ($images as $index => $image) {
                $product->images()->create(array_merge($image, ['order' => $index]));
            }
        }

        // Product pairing (cross-sell)
        $racket  = Product::where('slug', 'yonex-nanoflare-racket')->first();
        $grip    = Product::where('slug', 'racket-grip-tape-3pk')->first();
        $tee     = Product::where('slug', 'bsa-training-tee')->first();
        $shorts  = Product::where('slug', 'bsa-shorts-pro')->first();

        if ($racket && $grip) {
            $racket->pairedWith()->attach($grip->id);
        }
        if ($tee && $shorts) {
            $tee->pairedWith()->attach($shorts->id);
        }
    }
}
