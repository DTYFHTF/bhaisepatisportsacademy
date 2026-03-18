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
            // ── SKINCARE ────────────────────────────────────────────────────
            [
                'slug'             => 'aloe-vera-soothing-gel',
                'name'             => 'Aloe Vera Soothing Gel',
                'tagline'          => 'Instant post-wax relief',
                'price'            => 85000,
                'compare_at_price' => 120000,
                'category'         => Category::SKINCARE,
                'description'      => 'A lightweight, fast-absorbing aloe vera gel that calms redness and irritation after any waxing session. Perfect for sensitive skin.',
                'ingredients'      => 'Aloe Vera Extract, Glycerin, Vitamin E, Chamomile Extract, Purified Water',
                'tags'             => ['skincare', 'bestseller', 'aftercare'],
                'variants'         => [['100ml', 85000, 30], ['200ml', 150000, 20]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1617897903246-719242758050?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Aloe Vera Soothing Gel — 100ml'],
                ],
            ],
            [
                'slug'             => 'pre-wax-cleansing-oil',
                'name'             => 'Pre-Wax Cleansing Oil',
                'tagline'          => 'Prep your skin, perfect results',
                'price'            => 95000,
                'compare_at_price' => null,
                'category'         => Category::SKINCARE,
                'description'      => 'A gentle cleansing oil that removes dirt and excess oil before waxing, ensuring better wax adhesion and cleaner results.',
                'ingredients'      => 'Jojoba Oil, Tea Tree Oil, Vitamin E, Sweet Almond Oil',
                'tags'             => ['skincare', 'new-arrival'],
                'variants'         => [['150ml', 95000, 25]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Pre-Wax Cleansing Oil — 150ml'],
                ],
            ],
            [
                'slug'             => 'ingrown-hair-serum',
                'name'             => 'Ingrown Hair Serum',
                'tagline'          => 'Smooth skin, no bumps',
                'price'            => 120000,
                'compare_at_price' => 150000,
                'category'         => Category::SKINCARE,
                'description'      => 'A targeted serum with salicylic acid and tea tree to prevent and treat ingrown hairs. Apply daily after waxing.',
                'ingredients'      => 'Salicylic Acid (2%), Tea Tree Oil, Witch Hazel, Niacinamide, Purified Water',
                'tags'             => ['skincare', 'bestseller'],
                'variants'         => [['30ml', 120000, 35]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Ingrown Hair Serum — 30ml'],
                ],
            ],
            [
                'slug'             => 'moisturizing-body-lotion',
                'name'             => 'Moisturizing Body Lotion',
                'tagline'          => 'Hydrate and glow',
                'price'            => 75000,
                'compare_at_price' => null,
                'category'         => Category::SKINCARE,
                'description'      => 'A rich yet non-greasy body lotion that hydrates and nourishes freshly waxed skin. Light peach scent.',
                'ingredients'      => 'Shea Butter, Coconut Oil, Vitamin E, Peach Extract, Hyaluronic Acid',
                'tags'             => ['skincare', 'essential'],
                'variants'         => [['200ml', 75000, 40], ['400ml', 130000, 15]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Moisturizing Body Lotion — 200ml'],
                ],
            ],

            // ── AFTERCARE ───────────────────────────────────────────────────
            [
                'slug'             => 'post-wax-cooling-spray',
                'name'             => 'Post-Wax Cooling Spray',
                'tagline'          => 'Cool, calm, collected skin',
                'price'            => 65000,
                'compare_at_price' => 85000,
                'category'         => Category::AFTERCARE,
                'description'      => 'A refreshing mist that instantly cools and soothes skin after waxing. Contains menthol and chamomile for quick relief.',
                'ingredients'      => 'Chamomile Extract, Menthol, Aloe Vera, Purified Water, Witch Hazel',
                'tags'             => ['aftercare', 'new-arrival', 'bestseller'],
                'variants'         => [['100ml', 65000, 50]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Post-Wax Cooling Spray — 100ml'],
                ],
            ],
            [
                'slug'             => 'exfoliating-scrub',
                'name'             => 'Gentle Exfoliating Scrub',
                'tagline'          => 'Prevent bumps, stay smooth',
                'price'            => 110000,
                'compare_at_price' => null,
                'category'         => Category::AFTERCARE,
                'description'      => 'A fine-grain scrub for use 48 hours after waxing. Prevents ingrown hairs and keeps skin silky smooth between sessions.',
                'ingredients'      => 'Walnut Shell Powder, Coconut Oil, Vitamin E, Lavender Oil, Green Tea Extract',
                'tags'             => ['aftercare', 'essential'],
                'variants'         => [['150g', 110000, 25]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1599305445671-ac291c95aaa9?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Gentle Exfoliating Scrub — 150g'],
                ],
            ],
            [
                'slug'             => 'calming-face-mask',
                'name'             => 'Calming Face Mask',
                'tagline'          => 'Post-facial glow boost',
                'price'            => 95000,
                'compare_at_price' => 120000,
                'category'         => Category::AFTERCARE,
                'description'      => 'A soothing clay mask with kaolin and rose extract. Perfect after facial waxing or as a weekly skin treat.',
                'ingredients'      => 'Kaolin Clay, Rose Extract, Honey, Oat Extract, Glycerin',
                'tags'             => ['aftercare', 'new-arrival'],
                'variants'         => [['50g', 95000, 20], ['100g', 165000, 12]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1590439471364-192aa70c0b53?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Calming Face Mask — 50g'],
                ],
            ],

            // ── WAX KITS ────────────────────────────────────────────────────
            [
                'slug'             => 'at-home-wax-kit-rica',
                'name'             => 'At-Home Rica Wax Kit',
                'tagline'          => 'Salon results at home',
                'price'            => 250000,
                'compare_at_price' => 320000,
                'category'         => Category::WAX_KIT,
                'description'      => 'Everything you need for a professional Rica wax at home. Includes wax, warmer, pre-wax lotion, post-wax oil, and instructions.',
                'ingredients'      => 'Rica Wax (200g), Pre-Wax Lotion (50ml), Post-Wax Oil (50ml), Reusable Spatulas (3), Instruction Card',
                'tags'             => ['wax-kit', 'bestseller', 'new-arrival'],
                'variants'         => [['Standard', 250000, 15]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1607748862156-7c548e7e98f4?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'At-Home Rica Wax Kit'],
                ],
            ],
            [
                'slug'             => 'brow-shaping-kit',
                'name'             => 'Brow Shaping Kit',
                'tagline'          => 'Perfect brows, your way',
                'price'            => 85000,
                'compare_at_price' => null,
                'category'         => Category::WAX_KIT,
                'description'      => 'A precision brow shaping kit with mini wax strips, tweezers, and brow gel. For touch-ups between salon visits.',
                'ingredients'      => 'Mini Wax Strips (20), Precision Tweezers, Brow Gel (5ml), Mirror',
                'tags'             => ['wax-kit', 'essential'],
                'variants'         => [['Standard', 85000, 30]],
                'images'           => [
                    ['url' => 'https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=800&h=1000&q=80', 'alt_text' => 'Brow Shaping Kit'],
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
                    'sku'            => 'PP-' . $skuSlug . '-' . $skuLabel,
                    'price_override' => $priceOverride,
                    'stock'          => $stock,
                ]);
            }

            foreach ($images as $index => $image) {
                $product->images()->create(array_merge($image, ['order' => $index]));
            }
        }

        // Product pairing
        $aloeGel  = Product::where('slug', 'aloe-vera-soothing-gel')->first();
        $cooling  = Product::where('slug', 'post-wax-cooling-spray')->first();
        $ingrown  = Product::where('slug', 'ingrown-hair-serum')->first();
        $scrub    = Product::where('slug', 'exfoliating-scrub')->first();

        if ($aloeGel && $cooling) {
            $aloeGel->pairedWith()->attach($cooling->id);
        }
        if ($ingrown && $scrub) {
            $ingrown->pairedWith()->attach($scrub->id);
        }
    }
}
