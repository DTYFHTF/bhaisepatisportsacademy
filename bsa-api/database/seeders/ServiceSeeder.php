<?php

namespace Database\Seeders;

use App\Enums\ServiceCategory;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Curated Unsplash photo IDs matching each service category.
     * All images are free-to-use and contextually relevant to beauty / waxing services.
     * Replace any of these with actual Bhaisepati Sports Academy photos by updating the URL or
     * uploading to Cloudinary and setting cloudinary_id on the product image record.
     */
    private array $waxingPool = [
        'photo-1560066984-138dadb4c035', // spa / beauty treatment
        'photo-1519415943484-9fa1873496d4', // body waxing / smooth skin
        'photo-1522337360788-8b13dee7a37e', // beauty salon
        'photo-1515377905703-c4788e51af15', // hands / arm care
        'photo-1487412947147-5cebf100ffc2', // skincare / beauty
        'photo-1571875257727-256c39da42af', // spa body treatment
        'photo-1540555700478-4be289fbecef', // body spa session
        'photo-1552693673-1bf795851eba', // skin close-up
    ];

    private array $facialPool = [
        'photo-1616394584738-fc6e612e71b9', // facial treatment
        'photo-1512290923902-8a9f81dc236c', // face skincare
        'photo-1598440947619-2c35fc9a1f41', // facial beauty routine
        'photo-1570172619644-dfd03ed5d881', // face mask / facial
    ];

    private array $browPool = [
        'photo-1552693673-1bf795851eba', // close-up brow area
        'photo-1487412947147-5cebf100ffc2', // eye / brow beauty
        'photo-1512290923902-8a9f81dc236c', // face treatment
    ];

    private array $bodyCarePool = [
        'photo-1540555700478-4be289fbecef', // body spa
        'photo-1519415943484-9fa1873496d4', // smooth skin
        'photo-1571875257727-256c39da42af', // body treatment
        'photo-1560066984-138dadb4c035', // spa session
    ];

    private function imgs(string $slug, int $count = 4, string $category = 'waxing'): array
    {
        $pool = match ($category) {
            'facial'    => $this->facialPool,
            'brow'      => $this->browPool,
            'body_care' => $this->bodyCarePool,
            default     => $this->waxingPool,
        };

        // Deterministic starting offset per slug so each service gets a unique lead image.
        $start = abs(crc32($slug)) % count($pool);
        $urls  = [];
        for ($i = 0; $i < $count; $i++) {
            $id     = $pool[($start + $i) % count($pool)];
            $urls[] = "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=800&h=1000&q=80";
        }
        return $urls;
    }

    public function run(): void
    {
        $services = [
            // ── WAXING ──────────────────────────────────────────────────────
            [
                'slug'        => 'full-arms-wax',
                'name'        => 'Full Arms Wax',
                'description' => 'Complete waxing for both arms from shoulder to wrist. Smooth, clean results every time.',
                'category'    => ServiceCategory::WAXING,
                'duration'    => 30,
                'price'       => 80000,
                'wax_types'   => ['Rica', 'Honey', 'Chocolate'],
                'is_popular'  => true,
                'sort_order'  => 1,
                'images'      => $this->imgs('full-arms-wax'),
            ],
            [
                'slug'        => 'full-legs-wax',
                'name'        => 'Full Legs Wax',
                'description' => 'Both legs from hip to ankle. Our most popular service for consistently smooth skin.',
                'category'    => ServiceCategory::WAXING,
                'duration'    => 45,
                'price'       => 120000,
                'wax_types'   => ['Rica', 'Honey', 'Chocolate'],
                'is_popular'  => true,
                'sort_order'  => 2,
                'images'      => $this->imgs('full-legs-wax'),
            ],
            [
                'slug'        => 'half-legs-wax',
                'name'        => 'Half Legs Wax',
                'description' => 'Lower legs from knee to ankle. Quick and effective for regular maintenance.',
                'category'    => ServiceCategory::WAXING,
                'duration'    => 25,
                'price'       => 60000,
                'wax_types'   => ['Rica', 'Honey'],
                'is_popular'  => false,
                'sort_order'  => 3,
                'images'      => $this->imgs('half-legs-wax'),
            ],
            [
                'slug'        => 'underarm-wax',
                'name'        => 'Underarm Wax',
                'description' => 'Clean, precise underarm waxing. Fast and virtually pain-free with our premium wax.',
                'category'    => ServiceCategory::WAXING,
                'duration'    => 15,
                'price'       => 30000,
                'wax_types'   => ['Rica', 'Honey', 'Chocolate', 'Sugar'],
                'is_popular'  => true,
                'sort_order'  => 4,
                'images'      => $this->imgs('underarm-wax'),
            ],
            [
                'slug'        => 'full-body-wax',
                'name'        => 'Full Body Wax',
                'description' => 'Complete body waxing — arms, legs, underarms, and stomach. Our signature treatment.',
                'category'    => ServiceCategory::WAXING,
                'duration'    => 90,
                'price'       => 250000,
                'wax_types'   => ['Rica', 'Chocolate'],
                'is_popular'  => true,
                'sort_order'  => 5,
                'images'      => $this->imgs('full-body-wax', 5),
            ],
            [
                'slug'        => 'stomach-wax',
                'name'        => 'Stomach Wax',
                'description' => 'Gentle waxing for the stomach area. Great as an add-on to any body wax service.',
                'category'    => ServiceCategory::WAXING,
                'duration'    => 20,
                'price'       => 40000,
                'wax_types'   => ['Rica', 'Honey'],
                'is_popular'  => false,
                'sort_order'  => 6,
                'images'      => $this->imgs('stomach-wax', 3),
            ],
            [
                'slug'        => 'back-wax',
                'name'        => 'Back Wax',
                'description' => 'Full back waxing from shoulders to waist. Clean, thorough coverage.',
                'category'    => ServiceCategory::WAXING,
                'duration'    => 30,
                'price'       => 80000,
                'wax_types'   => ['Rica', 'Honey'],
                'is_popular'  => false,
                'sort_order'  => 7,
                'images'      => $this->imgs('back-wax', 3),
            ],

            // ── FACIAL ──────────────────────────────────────────────────────
            [
                'slug'        => 'upper-lip-wax',
                'name'        => 'Upper Lip Wax',
                'description' => 'Precise upper lip hair removal. Quick, clean, and gentle on sensitive facial skin.',
                'category'    => ServiceCategory::FACIAL,
                'duration'    => 10,
                'price'       => 15000,
                'wax_types'   => ['Rica', 'Sugar'],
                'is_popular'  => true,
                'sort_order'  => 8,
                'images'      => $this->imgs('upper-lip-wax', 3, 'facial'),
            ],
            [
                'slug'        => 'chin-wax',
                'name'        => 'Chin Wax',
                'description' => 'Targeted chin hair removal with our gentlest wax formulas.',
                'category'    => ServiceCategory::FACIAL,
                'duration'    => 10,
                'price'       => 15000,
                'wax_types'   => ['Rica', 'Sugar'],
                'is_popular'  => false,
                'sort_order'  => 9,
                'images'      => $this->imgs('chin-wax', 3, 'facial'),
            ],
            [
                'slug'        => 'full-face-wax',
                'name'        => 'Full Face Wax',
                'description' => 'Complete facial waxing — forehead, cheeks, upper lip, chin, and sideburns.',
                'category'    => ServiceCategory::FACIAL,
                'duration'    => 25,
                'price'       => 50000,
                'wax_types'   => ['Rica', 'Sugar'],
                'is_popular'  => true,
                'sort_order'  => 10,
                'images'      => $this->imgs('full-face-wax', 4, 'facial'),
            ],

            // ── BROW ────────────────────────────────────────────────────────
            [
                'slug'        => 'eyebrow-threading',
                'name'        => 'Eyebrow Threading',
                'description' => 'Precise eyebrow shaping using the threading technique. Clean lines, natural shape.',
                'category'    => ServiceCategory::BROW,
                'duration'    => 15,
                'price'       => 20000,
                'wax_types'   => [],
                'is_popular'  => true,
                'sort_order'  => 11,
                'images'      => $this->imgs('eyebrow-threading', 4, 'brow'),
            ],
            [
                'slug'        => 'eyebrow-wax',
                'name'        => 'Eyebrow Wax',
                'description' => 'Clean eyebrow shaping using premium wax. Smooth finish with longer-lasting results.',
                'category'    => ServiceCategory::BROW,
                'duration'    => 15,
                'price'       => 25000,
                'wax_types'   => ['Rica'],
                'is_popular'  => false,
                'sort_order'  => 12,
                'images'      => $this->imgs('eyebrow-wax', 3, 'brow'),
            ],

            // ── BODY CARE ───────────────────────────────────────────────────
            [
                'slug'        => 'body-scrub-treatment',
                'name'        => 'Body Scrub Treatment',
                'description' => 'Full body exfoliation with natural scrub. Removes dead skin, prep for waxing or standalone glow treatment.',
                'category'    => ServiceCategory::BODY_CARE,
                'duration'    => 40,
                'price'       => 100000,
                'wax_types'   => [],
                'is_popular'  => false,
                'sort_order'  => 13,
                'images'      => $this->imgs('body-scrub', 4, 'body_care'),
            ],
            [
                'slug'        => 'moisturizing-treatment',
                'name'        => 'Moisturizing Treatment',
                'description' => 'Deep hydration treatment for dry skin. Applied post-wax or as a standalone service.',
                'category'    => ServiceCategory::BODY_CARE,
                'duration'    => 30,
                'price'       => 80000,
                'wax_types'   => [],
                'is_popular'  => false,
                'sort_order'  => 14,
                'images'      => $this->imgs('moisturizing-treatment', 4, 'body_care'),
            ],
        ];

        Service::truncate();

        foreach ($services as $data) {
            Service::create($data);
        }
    }
}
