<?php

namespace Database\Seeders;

use App\Models\ScheduleSlot;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        ScheduleSlot::truncate();

        $slots = [
            ['day' => 'Sunday',    'time' => '6:00 AM – 8:00 AM',  'program_name' => 'Badminton Foundation',    'court' => 'Court 1 & 2', 'coach' => 'Coach Ramesh', 'level' => 'beginner',     'sort_order' => 1],
            ['day' => 'Sunday',    'time' => '8:00 AM – 10:00 AM', 'program_name' => 'Intermediate Development', 'court' => 'Court 1 & 2', 'coach' => 'Coach Sunil',  'level' => 'intermediate', 'sort_order' => 2],
            ['day' => 'Sunday',    'time' => '4:00 PM – 6:00 PM',  'program_name' => 'Youth Academy',           'court' => 'Court 1',     'coach' => 'Coach Ramesh', 'level' => 'beginner',     'sort_order' => 3],
            ['day' => 'Monday',    'time' => '6:00 AM – 8:00 AM',  'program_name' => 'Advanced & Competitive',  'court' => 'All Courts',  'coach' => 'Coach Sunil',  'level' => 'advanced',     'sort_order' => 4],
            ['day' => 'Monday',    'time' => '4:00 PM – 6:00 PM',  'program_name' => 'Badminton Foundation',    'court' => 'Court 1 & 2', 'coach' => 'Coach Ramesh', 'level' => 'beginner',     'sort_order' => 5],
            ['day' => 'Tuesday',   'time' => '6:00 AM – 8:00 AM',  'program_name' => 'Intermediate Development', 'court' => 'Court 1 & 2', 'coach' => 'Coach Sunil', 'level' => 'intermediate', 'sort_order' => 6],
            ['day' => 'Tuesday',   'time' => '4:00 PM – 6:00 PM',  'program_name' => 'Youth Academy',           'court' => 'Court 1',     'coach' => 'Coach Ramesh', 'level' => 'beginner',     'sort_order' => 7],
            ['day' => 'Wednesday', 'time' => '6:00 AM – 8:00 AM',  'program_name' => 'Advanced & Competitive',  'court' => 'All Courts',  'coach' => 'Coach Sunil',  'level' => 'advanced',     'sort_order' => 8],
            ['day' => 'Wednesday', 'time' => '4:00 PM – 6:00 PM',  'program_name' => 'Badminton Foundation',    'court' => 'Court 1 & 2', 'coach' => 'Coach Ramesh', 'level' => 'beginner',     'sort_order' => 9],
            ['day' => 'Thursday',  'time' => '6:00 AM – 8:00 AM',  'program_name' => 'Intermediate Development', 'court' => 'Court 1 & 2', 'coach' => 'Coach Sunil', 'level' => 'intermediate', 'sort_order' => 10],
            ['day' => 'Thursday',  'time' => '4:00 PM – 6:00 PM',  'program_name' => 'Youth Academy',           'court' => 'Court 1',     'coach' => 'Coach Ramesh', 'level' => 'beginner',     'sort_order' => 11],
            ['day' => 'Friday',    'time' => '6:00 AM – 8:00 AM',  'program_name' => 'All Levels Open Play',    'court' => 'All Courts',  'coach' => 'Staff',        'level' => 'all',          'sort_order' => 12],
            ['day' => 'Friday',    'time' => '4:00 PM – 6:00 PM',  'program_name' => 'Match Practice',          'court' => 'All Courts',  'coach' => 'Coach Sunil',  'level' => 'intermediate', 'sort_order' => 13],
        ];

        ScheduleSlot::insert(array_map(fn($slot) => array_merge($slot, [
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]), $slots));
    }
}
