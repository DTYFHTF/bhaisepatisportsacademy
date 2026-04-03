<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user for Filament panel
        User::updateOrCreate(
            ['email' => 'admin@bsa.com'],
            ['name' => 'BSA Admin', 'password' => bcrypt('TermsofService1!2@')]
        );

        $this->call([
            SettingsSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            ServiceSeeder::class,
            ProgramSeeder::class,
            FacilitySeeder::class,
            ScheduleSeeder::class,
            TestimonialSeeder::class,
            KitchenSeeder::class,
            StatSeeder::class,
        ]);
    }
}
