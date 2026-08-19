<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'BSA Admin', 'email' => 'admin@bsa.com', 'role' => StaffRole::SuperAdmin, 'phone' => '9841000001'],
            ['name' => 'Rajesh Maharjan', 'email' => 'manager@bsa.com', 'role' => StaffRole::Manager, 'phone' => '9841000002'],
            ['name' => 'Sunita Shrestha', 'email' => 'accounts@bsa.com', 'role' => StaffRole::Accountant, 'phone' => '9841000003'],
            ['name' => 'Bikash Dangol', 'email' => 'desk1@bsa.com', 'role' => StaffRole::FrontDesk, 'phone' => '9841000004'],
            ['name' => 'Mina Tamang', 'email' => 'desk2@bsa.com', 'role' => StaffRole::FrontDesk, 'phone' => '9841000005'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => Hash::make('TermsofService1!2@'), 'is_active' => true],
            );
        }
    }
}
