<?php

namespace Database\Seeders;

use App\Models\Look;
use App\Models\Product;
use Illuminate\Database\Seeder;

class LookSeeder extends Seeder
{
    public function run(): void
    {
        $aloeGel  = Product::where('slug', 'aloe-vera-soothing-gel')->firstOrFail();
        $cooling  = Product::where('slug', 'post-wax-cooling-spray')->firstOrFail();
        $ingrown  = Product::where('slug', 'ingrown-hair-serum')->first();
        $scrub    = Product::where('slug', 'exfoliating-scrub')->firstOrFail();
        $cleanser = Product::where('slug', 'pre-wax-cleansing-oil')->firstOrFail();
        $lotion   = Product::where('slug', 'moisturizing-body-lotion')->firstOrFail();
        $mask     = Product::where('slug', 'calming-face-mask')->firstOrFail();

        // ── Look 1: The Pre-Wax Ritual (3 pieces) ─────────────────────────
        $look1 = Look::create([
            'look_hash'      => 'PRE10001',
            'display_name'   => 'The Pre-Wax Ritual',
            'phone_hash'     => hash_hmac('sha256', '9841234567', config('app.key')),
            'ai_explanation' => 'Prepare your skin for the best wax results. Start with the Pre-Wax Cleansing Oil to remove impurities, follow with the Exfoliating Scrub to lift dead skin, then finish with the Aloe Vera Soothing Gel for a calm, ready canvas.',
        ]);
        $look1->items()->createMany([
            ['product_id' => $cleanser->id, 'order' => 0],
            ['product_id' => $scrub->id,    'order' => 1],
            ['product_id' => $aloeGel->id,  'order' => 2],
        ]);

        // ── Look 2: The Post-Wax Recovery (2 pieces) ──────────────────────
        $look2 = Look::create([
            'look_hash'      => 'PST20002',
            'display_name'   => 'The Post-Wax Recovery',
            'ai_explanation' => 'Soothe and protect after waxing. The Post-Wax Cooling Spray instantly calms redness, while the Moisturizing Body Lotion locks in hydration and restores softness.',
        ]);
        $look2->items()->createMany([
            ['product_id' => $cooling->id, 'order' => 0],
            ['product_id' => $lotion->id,  'order' => 1],
        ]);

        // ── Look 3: The Glow Routine (3 pieces) ───────────────────────────
        $look3 = Look::create([
            'look_hash'      => 'GLW30003',
            'display_name'   => 'The Glow Routine',
            'ai_explanation' => 'Your complete skin glow kit. The Calming Face Mask deep-cleanses and soothes, the Aloe Vera Soothing Gel replenishes moisture, and if needed the Ingrown Hair Serum keeps skin bump-free and radiant.',
        ]);
        $look3->items()->createMany([
            ['product_id' => $mask->id,    'order' => 0],
            ['product_id' => $aloeGel->id, 'order' => 1],
            ['product_id' => $ingrown->id, 'order' => 2],
        ]);
    }
}
