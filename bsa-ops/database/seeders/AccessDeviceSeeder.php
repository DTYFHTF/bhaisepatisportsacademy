<?php

namespace Database\Seeders;

use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use App\Models\AccessCredential;
use App\Models\AccessDevice;
use App\Models\Department;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccessDeviceSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20260723);

        $departments = Department::pluck('id', 'code');
        $desk = User::where('email', 'desk1@bsa.com')->first();

        $devices = [
            ['name' => 'Gym main door', 'device_uid' => 'BSA-DOOR-GYM-01', 'type' => 'door_controller', 'department' => 'GYM'],
            ['name' => 'Pool turnstile', 'device_uid' => 'BSA-TURN-POOL-01', 'type' => 'turnstile', 'department' => 'POOL'],
            ['name' => 'Front-desk kiosk', 'device_uid' => 'BSA-KIOSK-01', 'type' => 'kiosk', 'department' => null],
        ];

        foreach ($devices as $device) {
            AccessDevice::updateOrCreate(
                ['device_uid' => $device['device_uid']],
                [
                    'name' => $device['name'],
                    'type' => $device['type'],
                    'department_id' => $device['department'] ? $departments[$device['department']] : null,
                    'direction' => 'entry',
                    'is_active' => true,
                ],
            );
        }

        // RFID cards for ~40 members. Raw identifiers follow BSA-CARD-{code}
        // so demo hardware (and tests) can derive them; only hashes are stored.
        $members = Member::orderBy('member_code')->limit(40)->get();

        foreach ($members as $member) {
            $raw = 'BSA-CARD-' . $member->member_code; // e.g. BSA-CARD-BSA-00001

            AccessCredential::updateOrCreate(
                ['identifier_hash' => AccessCredential::hashIdentifier($raw)],
                [
                    'member_id' => $member->id,
                    'type' => CredentialType::RfidCard,
                    'identifier_hint' => substr($member->member_code, -4),
                    'label' => 'RFID card',
                    'deposit_amount' => 20000, // NPR 200
                    'status' => CredentialStatus::Active,
                    'issued_at' => $member->joined_on,
                    'issued_by' => $desk?->id,
                ],
            );
        }

        $this->command->info('Devices: ' . AccessDevice::count() . ' | Credentials: ' . AccessCredential::count());
    }
}
