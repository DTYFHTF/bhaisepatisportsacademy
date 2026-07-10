<?php

namespace Database\Seeders;

use App\Models\Coach;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoachSeeder extends Seeder
{
    // image_url is intentionally left null — the site shows a branded initials
    // avatar until an owner uploads a real headshot via the admin (Coaches → Photo).
    public function run(): void
    {
        Coach::truncate();

        $coaches = [
            [
                'slug'             => 'ramesh-shrestha',
                'name'             => 'Ramesh Shrestha',
                'role'             => 'Head Badminton Coach',
                'bio'              => 'Ramesh leads BSA\'s badminton programs with over a decade on court. He built his coaching around strong fundamentals — footwork, grip, and match temperament — and has guided beginners all the way to district-level competition.',
                'credentials'      => 'BWF Level 2 Certified',
                'experience_years' => 12,
                'specialties'      => ['Fundamentals', 'Footwork', 'Youth coaching'],
                'is_active'        => true,
                'sort_order'       => 1,
            ],
            [
                'slug'             => 'sunita-rai',
                'name'             => 'Sunita Rai',
                'role'             => 'Youth & Competitive Coach',
                'bio'              => 'A former national-level player, Sunita specialises in developing young talent and preparing competitive players for tournament play. Her sessions are high-energy and technique-focused.',
                'credentials'      => 'Ex-National Player · Level 1 Certified',
                'experience_years' => 8,
                'specialties'      => ['Singles', 'Tournament prep', 'Junior development'],
                'is_active'        => true,
                'sort_order'       => 2,
            ],
            [
                'slug'             => 'bikash-tamang',
                'name'             => 'Bikash Tamang',
                'role'             => 'Strength & Conditioning Coach',
                'bio'              => 'Bikash runs the gym floor and the recovery programs. He designs strength and conditioning plans that keep athletes powerful and injury-free, and integrates recovery work through the sauna and mobility routines.',
                'credentials'      => 'Certified S&C Specialist',
                'experience_years' => 7,
                'specialties'      => ['Strength training', 'Injury prevention', 'Recovery'],
                'is_active'        => true,
                'sort_order'       => 3,
            ],
        ];

        foreach ($coaches as $coach) {
            Coach::create(array_merge(['id' => Str::uuid()], $coach));
        }
    }
}
