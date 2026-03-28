<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgramSeeder extends Seeder
{
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
                'features'          => json_encode(['Basic stroke techniques', 'Court movement & footwork', 'Rules & scoring', 'Equipment guidance']),
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
                'features'          => json_encode(['Advanced stroke techniques', 'Rally strategy', 'Match play & doubles', 'Video analysis']),
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
                'features'          => json_encode(['Tournament preparation', 'Speed & agility drills', 'Match simulation', 'Fitness integration']),
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
                'features'          => json_encode(['Fun-first approach', 'Age-appropriate drills', 'Mini tournaments', 'Physical literacy']),
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
                'features'          => json_encode(['Full gym access', 'Strength equipment', 'Cardio machines', 'Flexible schedule']),
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
                'features'          => json_encode(['Unlimited court booking', 'Full gym access', 'Sauna & steam access', 'Priority scheduling']),
            ],
        ];

        foreach ($programs as $program) {
            Program::create(array_merge(['id' => Str::uuid()], $program));
        }
    }
}
