<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable FK checks - compatible with both MySQL and SQLite
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } else {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }

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
            LookSeeder::class,
            StatSeeder::class,
        ]);

        // Re-enable FK checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            DB::statement('PRAGMA foreign_keys=ON;');
        }
    }
}
