<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    // image_url is intentionally left null in seed data — the site shows a
    // polished branded placeholder until an owner uploads a real photo via
    // the admin (Facilities → Image). Uploaded images are served from Cloudinary.
    public function run(): void
    {
        Facility::truncate();

        $facilities = [
            [
                'slug'        => 'badminton-courts',
                'name'        => 'Badminton Courts',
                'description' => 'Professional-grade badminton courts with proper lighting, regulation markings, and quality playing surface. Available for training and court booking.',
                'category'    => 'BADMINTON',
                'icon'        => '🏸',
                'hours'       => '6:00 AM – 9:00 PM',
                'capacity'    => '3 courts',
                'sort_order'  => 1,
                'features'    => ['Professional playing surface', 'Proper court lighting', 'Regulation markings', 'Equipment available', 'Court booking system'],
            ],
            [
                'slug'        => 'gym-strength',
                'name'        => 'Gym & Strength Training',
                'description' => 'Fully equipped gym with modern strength training equipment. Designed for athletic conditioning and injury prevention.',
                'category'    => 'GYM',
                'icon'        => '💪',
                'hours'       => '6:00 AM – 9:00 PM',
                'capacity'    => '20+ members',
                'sort_order'  => 2,
                'features'    => ['Modern equipment', 'Free weights & machines', 'Cardio zone', 'Mirrors & ventilation', 'Personal training available'],
            ],
            [
                'slug'        => 'sauna-steam',
                'name'        => 'Sauna & Steam Room',
                'description' => 'Recover faster after intense training sessions. Our sauna and steam facilities help with muscle recovery and relaxation.',
                'category'    => 'SAUNA',
                'icon'        => '♨️',
                'hours'       => '7:00 AM – 8:00 PM',
                'capacity'    => '8 people',
                'sort_order'  => 3,
                'features'    => ['Sauna therapy', 'Steam room', 'Muscle recovery', 'Post-workout relaxation', 'Clean & hygienic'],
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::create(array_merge(['id' => Str::uuid()], $facility));
        }
    }
}
