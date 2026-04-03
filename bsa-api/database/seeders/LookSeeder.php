<?php

namespace Database\Seeders;

use App\Models\Look;
use App\Models\Product;
use Illuminate\Database\Seeder;

class LookSeeder extends Seeder
{
    public function run(): void
    {
        $racket   = Product::where('slug', 'yonex-nanoflare-racket')->firstOrFail();
        $shuttles = Product::where('slug', 'feather-shuttlecocks-12pk')->firstOrFail();
        $grip     = Product::where('slug', 'racket-grip-tape-3pk')->firstOrFail();
        $tee      = Product::where('slug', 'bsa-training-tee')->firstOrFail();
        $shorts   = Product::where('slug', 'bsa-shorts-pro')->firstOrFail();
        $protein  = Product::where('slug', 'whey-protein-1kg')->firstOrFail();
        $electro  = Product::where('slug', 'electrolyte-sachets-20pk')->firstOrFail();

        // ── Look 1: Court Starter Kit (3 pieces) ──────────────────────────
        $look1 = Look::create([
            'look_hash'      => 'KIT10001',
            'display_name'   => 'Court Starter Kit',
            'phone_hash'     => hash_hmac('sha256', '9841234567', config('app.key')),
            'ai_explanation' => 'Everything you need to start playing. The Nanoflare racket delivers speed and control, feather shuttlecocks give authentic flight, and fresh grip tape ensures a secure hold.',
        ]);
        $look1->items()->createMany([
            ['product_id' => $racket->id,   'order' => 0],
            ['product_id' => $shuttles->id, 'order' => 1],
            ['product_id' => $grip->id,     'order' => 2],
        ]);

        // ── Look 2: Match Day Outfit (2 pieces) ──────────────────────────
        $look2 = Look::create([
            'look_hash'      => 'OUT20002',
            'display_name'   => 'Match Day Outfit',
            'ai_explanation' => 'Look and perform your best on match day. The BSA Training Tee keeps you cool with moisture-wicking fabric, paired with lightweight Court Shorts for full range of movement.',
        ]);
        $look2->items()->createMany([
            ['product_id' => $tee->id,    'order' => 0],
            ['product_id' => $shorts->id, 'order' => 1],
        ]);

        // ── Look 3: Recovery Bundle (3 pieces) ────────────────────────────
        $look3 = Look::create([
            'look_hash'      => 'REC30003',
            'display_name'   => 'Recovery Bundle',
            'ai_explanation' => 'Recover smarter after every session. Whey protein rebuilds muscle, electrolytes rehydrate fast, and the Training Tee is ready for your next workout.',
        ]);
        $look3->items()->createMany([
            ['product_id' => $protein->id, 'order' => 0],
            ['product_id' => $electro->id, 'order' => 1],
            ['product_id' => $tee->id,     'order' => 2],
        ]);
    }
}
