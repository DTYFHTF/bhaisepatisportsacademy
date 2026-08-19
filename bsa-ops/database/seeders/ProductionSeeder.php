<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Reference data only — safe to run on every deploy.
 *
 * Deliberately excludes the demo seeders (members, subscriptions, POS
 * history): production starts empty and fills up with real business.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // All idempotent (updateOrCreate), so re-running changes nothing.
        $this->call([
            SettingsSeeder::class,
            DepartmentSeeder::class,
            ExpenseCategorySeeder::class,
        ]);

        // The first administrator, created once. Never touched again, so a
        // later deploy cannot reset a password the academy has changed.
        if (! User::query()->exists()) {
            User::create([
                'name' => 'BSA Admin',
                'email' => 'admin@bsa.com',
                'password' => Hash::make('TermsofService1!2@'),
                'role' => StaffRole::SuperAdmin,
                'is_active' => true,
            ]);

            $this->command?->warn('Created the first admin (admin@bsa.com). Change this password now.');
        }
    }
}
