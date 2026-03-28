<?php

namespace Database\Seeders;

use App\Enums\ServiceCategory;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * BSA sports facility image pool (free Unsplash images).
     * Replace with actual BSA photos (upload to Cloudinary) when available.
     */
    private array $badmintonPool = [
        'photo-1613914153966-fd0cf11e0e8b', // badminton court
        'photo-1606107557195-0e29a4b5b4aa', // shuttlecock
        'photo-1551698618-1dfe5d97d256', // badminton racket
        'photo-1547347298-4074fc3086f0', // sports court overhead
    ];

    private array $gymPool = [
        'photo-1534438327276-14e5300c3a48', // gym equipment
        'photo-1571019613454-1cb2f99b2d8b', // person exercising
        'photo-1583454110551-21f2fa2afe61', // weights/barbell
        'photo-1540497077202-7c8a3999166f', // gym interior
    ];

    private array $saunaPool = [
        'photo-1520974048-a3a50c2b0eb0', // sauna interior
        'photo-1531306728370-e2ebd9d7bb99', // steam room
        'photo-1587271407850-8d438ca9fdf2', // relaxation spa
        'photo-1544161515-4ab6ce6db874', // recovery therapy
    ];

    private function imgs(string $slug, ServiceCategory $cat, int $count = 3): array
    {
        $pool = match ($cat) {
            ServiceCategory::GYM   => $this->gymPool,
            ServiceCategory::SAUNA => $this->saunaPool,
            default                => $this->badmintonPool,
        };
        $start = abs(crc32($slug)) % count($pool);
        $urls  = [];
        for ($i = 0; $i < $count; $i++) {
            $id     = $pool[($start + $i) % count($pool)];
            $urls[] = "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=800&h=600&q=80";
        }
        return $urls;
    }

    public function run(): void
    {
        Service::truncate();

        $services = [
            // ── BADMINTON ──────────────────────────────────────────────────
            [
                'slug'        => 'court-booking-1hr',
                'name'        => 'Court Booking (1 Hour)',
                'description' => 'Book a professional badminton court for 1 hour. Suitable for casual play or personal practice.',
                'category'    => ServiceCategory::BADMINTON,
                'duration'    => 60,
                'price'       => 50000,
                'wax_types'   => [],
                'is_popular'  => true,
                'sort_order'  => 1,
                'images'      => $this->imgs('court-1hr', ServiceCategory::BADMINTON),
            ],
            [
                'slug'        => 'court-booking-2hrs',
                'name'        => 'Court Booking (2 Hours)',
                'description' => 'Extended 2-hour court booking. Perfect for doubles matches or group practice sessions.',
                'category'    => ServiceCategory::BADMINTON,
                'duration'    => 120,
                'price'       => 90000,
                'wax_types'   => [],
                'is_popular'  => true,
                'sort_order'  => 2,
                'images'      => $this->imgs('court-2hrs', ServiceCategory::BADMINTON),
            ],
            [
                'slug'        => 'personal-training-session',
                'name'        => 'Personal Training Session',
                'description' => 'One-on-one badminton coaching with an expert BSA coach. Tailored for your specific improvement goals.',
                'category'    => ServiceCategory::BADMINTON,
                'duration'    => 60,
                'price'       => 150000,
                'wax_types'   => [],
                'is_popular'  => true,
                'sort_order'  => 3,
                'images'      => $this->imgs('personal-training', ServiceCategory::BADMINTON),
            ],
            [
                'slug'        => 'group-session',
                'name'        => 'Group Session',
                'description' => 'Join a structured group training session on the court. Mix drills, rallies, and match play with other members.',
                'category'    => ServiceCategory::BADMINTON,
                'duration'    => 90,
                'price'       => 80000,
                'wax_types'   => [],
                'is_popular'  => false,
                'sort_order'  => 4,
                'images'      => $this->imgs('group-session', ServiceCategory::BADMINTON),
            ],

            // ── GYM ────────────────────────────────────────────────────────
            [
                'slug'        => 'gym-day-pass',
                'name'        => 'Gym Day Pass',
                'description' => 'Full day access to BSA\'s strength and conditioning gym. Use all equipment without a monthly commitment.',
                'category'    => ServiceCategory::GYM,
                'duration'    => 120,
                'price'       => 30000,
                'wax_types'   => [],
                'is_popular'  => true,
                'sort_order'  => 5,
                'images'      => $this->imgs('gym-day-pass', ServiceCategory::GYM),
            ],
            [
                'slug'        => 'strength-conditioning-session',
                'name'        => 'Strength & Conditioning Session',
                'description' => 'Guided 1-hour strength training session with a certified trainer. Build power and improve athletic performance.',
                'category'    => ServiceCategory::GYM,
                'duration'    => 60,
                'price'       => 120000,
                'wax_types'   => [],
                'is_popular'  => false,
                'sort_order'  => 6,
                'images'      => $this->imgs('strength-session', ServiceCategory::GYM),
            ],

            // ── SAUNA ──────────────────────────────────────────────────────
            [
                'slug'        => 'sauna-session',
                'name'        => 'Sauna Session (30 min)',
                'description' => 'Relax and recover in our sauna. Helps with muscle recovery, circulation, and post-workout soreness.',
                'category'    => ServiceCategory::SAUNA,
                'duration'    => 30,
                'price'       => 40000,
                'wax_types'   => [],
                'is_popular'  => true,
                'sort_order'  => 7,
                'images'      => $this->imgs('sauna', ServiceCategory::SAUNA),
            ],
            [
                'slug'        => 'sauna-gym-combo',
                'name'        => 'Gym + Sauna Combo',
                'description' => 'Gym day pass bundled with a sauna session. Train hard, recover right — best value combo.',
                'category'    => ServiceCategory::SAUNA,
                'duration'    => 150,
                'price'       => 60000,
                'wax_types'   => [],
                'is_popular'  => true,
                'sort_order'  => 8,
                'images'      => $this->imgs('sauna-gym-combo', ServiceCategory::SAUNA),
            ],
        ];

        foreach ($services as $data) {
            Service::create($data);
        }
    }
}

