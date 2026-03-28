<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        Testimonial::truncate();

        $testimonials = [
            [
                'name'       => 'Rajan Shrestha',
                'role'       => 'Competitive Player',
                'quote'      => 'BSA transformed my game. The coaching here is serious and the facilities are top-notch for Nepal.',
                'sort_order' => 1,
            ],
            [
                'name'       => 'Sita Maharjan',
                'role'       => 'Youth Academy Parent',
                'quote'      => 'My son loves coming here. The coaches know how to make training fun while teaching real skills.',
                'sort_order' => 2,
            ],
            [
                'name'       => 'Bikash Tamang',
                'role'       => 'Gym Member',
                'quote'      => 'Best gym in Bhaisepati. Clean, well-equipped, and the staff actually cares about your progress.',
                'sort_order' => 3,
            ],
            [
                'name'       => 'Priya Thapa',
                'role'       => 'Badminton Intermediate',
                'quote'      => 'I joined as a complete beginner and within three months I was playing in doubles matches. Incredible coaches.',
                'sort_order' => 4,
            ],
            [
                'name'       => 'Aarav Bhandari',
                'role'       => 'Full Member',
                'quote'      => 'Court, gym, and sauna all in one place. The Full Membership is absolutely worth it for serious athletes.',
                'sort_order' => 5,
            ],
        ];

        Testimonial::insert(array_map(fn($t) => array_merge($t, [
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]), $testimonials));
    }
}
