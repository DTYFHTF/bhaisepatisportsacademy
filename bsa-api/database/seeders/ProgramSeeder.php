<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgramSeeder extends Seeder
{
    // image_url is intentionally left null in seed data — the site shows a
    // polished branded placeholder until an owner uploads a real photo via
    // the admin (Programs → Image). Uploaded images are served from Cloudinary.
    public function run(): void
    {
        Program::truncate();

        $programs = [
            [
                'slug'              => 'badminton-foundation',
                'name'              => 'Badminton Foundation',
                'description'       => 'Perfect for beginners. Learn grips, footwork, basic strokes, and court rules. Build a strong foundation for competitive play.',
                'category'          => 'BADMINTON',
                'level'             => 'beginner',
                'age_group'         => 'All ages',
                'duration'          => '1 month',
                'sessions_per_week' => 3,
                'price'             => 300000,
                'is_popular'        => true,
                'sort_order'        => 1,
                'coach_name'        => 'Coach Ramesh Shrestha',
                'highlight'         => 'Most popular for first-timers',
                'features'          => ['Basic stroke techniques', 'Court movement & footwork', 'Rules & scoring', 'Equipment guidance'],
            ],
            [
                'slug'              => 'badminton-intermediate',
                'name'              => 'Intermediate Development',
                'description'       => 'Refine your game. Advanced strokes, rally strategies, match play, and competitive mindset training.',
                'category'          => 'BADMINTON',
                'level'             => 'intermediate',
                'age_group'         => 'All ages',
                'duration'          => '1 month',
                'sessions_per_week' => 4,
                'price'             => 500000,
                'is_popular'        => true,
                'sort_order'        => 2,
                'coach_name'        => 'Coach Ramesh Shrestha',
                'highlight'         => 'Step up your rally game',
                'features'          => ['Advanced stroke techniques', 'Rally strategy', 'Match play & doubles', 'Video analysis'],
            ],
            [
                'slug'              => 'badminton-competitive',
                'name'              => 'Advanced & Competitive',
                'description'       => 'Tournament-ready training. Speed drills, endurance, tactical play, and match simulation under pressure.',
                'category'          => 'BADMINTON',
                'level'             => 'advanced',
                'age_group'         => '13+',
                'duration'          => '1 month',
                'sessions_per_week' => 5,
                'price'             => 800000,
                'is_popular'        => false,
                'sort_order'        => 3,
                'coach_name'        => 'Coach Sunita Rai',
                'highlight'         => 'Train like a tournament player',
                'features'          => ['Tournament preparation', 'Speed & agility drills', 'Match simulation', 'Fitness integration'],
            ],
            [
                'slug'              => 'youth-academy',
                'name'              => 'Youth Academy',
                'description'       => 'Age-appropriate training for junior players. Focus on fun, fundamentals, and building a love for the sport.',
                'category'          => 'BADMINTON',
                'level'             => 'beginner',
                'age_group'         => 'Under 16',
                'duration'          => '1 month',
                'sessions_per_week' => 3,
                'price'             => 250000,
                'is_popular'        => true,
                'sort_order'        => 4,
                'coach_name'        => 'Coach Sunita Rai',
                'highlight'         => 'Built for young players',
                'features'          => ['Fun-first approach', 'Age-appropriate drills', 'Mini tournaments', 'Physical literacy'],
            ],
            [
                'slug'              => 'gym-membership',
                'name'              => 'Gym & Strength Training',
                'description'       => 'Full gym access with strength training equipment. Build power, prevent injuries, and improve athletic performance.',
                'category'          => 'FITNESS',
                'level'             => 'all',
                'age_group'         => '16+',
                'duration'          => '1 month',
                'sessions_per_week' => 6,
                'price'             => 400000,
                'is_popular'        => true,
                'sort_order'        => 5,
                'coach_name'        => 'Coach Bikash Tamang',
                'highlight'         => 'Strength for every athlete',
                'features'          => ['Full gym access', 'Strength equipment', 'Cardio machines', 'Flexible schedule'],
            ],
            [
                'slug'              => 'full-membership',
                'name'              => 'Full Membership Package',
                'description'       => 'Complete access to everything BSA offers: badminton courts, gym, sauna & steam. The ultimate training package.',
                'category'          => 'FITNESS',
                'level'             => 'all',
                'age_group'         => '16+',
                'duration'          => '1 month',
                'sessions_per_week' => 7,
                'price'             => 600000,
                'is_popular'        => true,
                'sort_order'        => 6,
                'coach_name'        => 'All BSA coaches',
                'highlight'         => 'Everything, one price',
                'features'          => ['Unlimited court booking', 'Full gym access', 'Sauna & steam access', 'Priority scheduling'],
            ],
            // ── RECOVERY ─────────────────────────────────────────────────────
            [
                'slug'              => 'sauna-monthly',
                'name'              => 'Sauna & Steam Monthly',
                'description'       => 'Unlimited monthly access to our sauna and steam room. Perfect for post-workout recovery and general wellness.',
                'category'          => 'RECOVERY',
                'level'             => 'all',
                'age_group'         => 'All ages',
                'duration'          => '1 month',
                'sessions_per_week' => 7,
                'price'             => 150000,
                'is_popular'        => true,
                'sort_order'        => 7,
                'coach_name'        => null,
                'highlight'         => 'Recover, unlimited',
                'features'          => ['Unlimited sauna access', 'Steam room included', 'Muscle recovery support', 'Relaxation sessions', 'Clean towels provided'],
            ],
            [
                'slug'              => 'recovery-combo',
                'name'              => 'Recovery & Wellness Bundle',
                'description'       => 'A curated recovery package with sauna, steam, and guided stretching. Designed to support athletic longevity.',
                'category'          => 'RECOVERY',
                'level'             => 'all',
                'age_group'         => '16+',
                'duration'          => '1 month',
                'sessions_per_week' => 5,
                'price'             => 220000,
                'is_popular'        => false,
                'sort_order'        => 8,
                'coach_name'        => 'Coach Bikash Tamang',
                'highlight'         => 'Train hard, recover smart',
                'features'          => ['Sauna & steam access', 'Guided stretching sessions', 'Recovery nutrition tips', 'Flexibility coaching'],
            ],
            [
                'slug'              => 'sauna-day-pass',
                'name'              => 'Sauna Day Pass',
                'description'       => 'Single-day access to the sauna and steam room. No commitment, just pure recovery.',
                'category'          => 'RECOVERY',
                'level'             => 'all',
                'age_group'         => 'All ages',
                'duration'          => '1 session',
                'sessions_per_week' => 1,
                'price'             => 40000,
                'is_popular'        => true,
                'sort_order'        => 9,
                'coach_name'        => null,
                'highlight'         => 'No commitment, pure recovery',
                'features'          => ['Single sauna session', 'Steam room access', 'Locker use included', 'Shower facilities'],
            ],
        ];

        foreach ($programs as $program) {
            Program::create(array_merge(['id' => Str::uuid()], $program));
        }
    }
}
