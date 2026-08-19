<?php

namespace Database\Seeders;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Services\NumberSequenceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MemberSeeder extends Seeder
{
    private const FIRST_NAMES = [
        'male' => ['Anish', 'Bibek', 'Bikash', 'Dipesh', 'Kiran', 'Manish', 'Nabin', 'Prakash', 'Rajan', 'Roshan', 'Sagar', 'Sandeep', 'Santosh', 'Sujan', 'Suman', 'Milan', 'Niraj', 'Pratik', 'Aayush', 'Saroj'],
        'female' => ['Anita', 'Bina', 'Gita', 'Kabita', 'Laxmi', 'Mina', 'Nirmala', 'Puja', 'Rita', 'Sabina', 'Sarita', 'Shova', 'Sita', 'Srijana', 'Sunita', 'Alisha', 'Prativa', 'Rojina', 'Menuka', 'Bandana'],
    ];

    private const LAST_NAMES = [
        'Maharjan', 'Shrestha', 'Dangol', 'Tamang', 'Gurung', 'Karki', 'Sharma', 'KC', 'Awale', 'Bajracharya',
        'Manandhar', 'Thapa', 'Rai', 'Limbu', 'Basnet', 'Adhikari', 'Khadka', 'Prajapati', 'Shakya', 'Joshi',
    ];

    private const TOLES = ['Bhaisepati Height', 'Sainbu Awas', 'Khumaltar Chowk', 'Bungamati Road', 'Nakhu Bazaar', 'Ekantakuna', 'Dhapakhel Marg', 'Sunakothi', 'Chhampi Road', 'Harisiddhi'];

    private const MUNICIPALITIES = ['Lalitpur Metropolitan City', 'Godawari Municipality', 'Mahalaxmi Municipality'];

    private const OCCUPATIONS = ['Student', 'Banker', 'Engineer', 'Doctor', 'Teacher', 'Business owner', 'Government officer', 'Designer', 'Developer', 'Nurse', 'Pilot', 'Retired'];

    private const BLOOD_GROUPS = ['a_pos', 'a_pos', 'b_pos', 'b_pos', 'o_pos', 'o_pos', 'o_pos', 'ab_pos', 'a_neg', 'o_neg'];

    private const REFERRALS = ['walk_in', 'walk_in', 'walk_in', 'friend', 'friend', 'facebook', 'instagram', 'website', 'event', 'corporate'];

    public function run(): void
    {
        mt_srand(20260719); // deterministic fixtures

        $sequences = app(NumberSequenceService::class);
        $pick = fn (array $list) => $list[mt_rand(0, count($list) - 1)];

        for ($i = 0; $i < 60; $i++) {
            $gender = mt_rand(0, 99) < 62 ? 'male' : 'female';
            $firstName = $pick(self::FIRST_NAMES[$gender]);
            $lastName = $pick(self::LAST_NAMES);

            // Ages: a few minors, mostly 18-50, some older.
            $age = match (true) {
                $i < 6 => mt_rand(12, 17),
                $i < 45 => mt_rand(18, 40),
                default => mt_rand(41, 62),
            };

            $joinedOn = Carbon::today()->subDays(mt_rand(7, 420));
            $isMinor = $age < 18;
            $phone = '98' . str_pad((string) mt_rand(10000000, 69999999), 8, '0', STR_PAD_LEFT);

            Member::create([
                'member_code' => $sequences->memberCode(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => Carbon::today()->subYears($age)->subDays(mt_rand(0, 364)),
                'gender' => $gender,
                'blood_group' => mt_rand(0, 9) < 8 ? $pick(self::BLOOD_GROUPS) : null,
                'phone' => $phone,
                'alt_phone' => mt_rand(0, 9) < 3 ? '98' . mt_rand(10000000, 69999999) : null,
                'email' => mt_rand(0, 9) < 5 ? strtolower("{$firstName}.{$lastName}" . mt_rand(1, 99)) . '@gmail.com' : null,
                'province' => 'Bagmati',
                'district' => 'Lalitpur',
                'municipality' => $pick(self::MUNICIPALITIES),
                'ward_no' => mt_rand(1, 29),
                'tole' => $pick(self::TOLES),
                'occupation' => $isMinor ? 'Student' : $pick(self::OCCUPATIONS),
                'emergency_contact_name' => $pick(self::FIRST_NAMES[mt_rand(0, 1) ? 'male' : 'female']) . ' ' . $lastName,
                'emergency_contact_relation' => $pick(['Spouse', 'Father', 'Mother', 'Brother', 'Sister']),
                'emergency_contact_phone' => '98' . mt_rand(10000000, 69999999),
                'guardian_name' => $isMinor ? $pick(self::FIRST_NAMES['male']) . ' ' . $lastName : null,
                'guardian_relation' => $isMinor ? $pick(['Father', 'Mother']) : null,
                'guardian_phone' => $isMinor ? '98' . mt_rand(10000000, 69999999) : null,
                'govt_id_type' => $isMinor ? null : (mt_rand(0, 9) < 7 ? 'citizenship' : 'national_id'),
                'govt_id_number' => $isMinor ? null : mt_rand(10000, 99999) . '-' . mt_rand(100, 9999),
                'medical_conditions' => mt_rand(0, 19) === 0 ? 'Mild asthma' : null,
                'allergies' => mt_rand(0, 24) === 0 ? 'Dust allergy' : null,
                'referral_source' => $pick(self::REFERRALS),
                'marketing_consent' => mt_rand(0, 9) < 6,
                'status' => MemberStatus::Active, // rolled up after subscriptions seed
                'joined_on' => $joinedOn,
                'created_at' => $joinedOn,
            ]);
        }

        // One blacklisted member for realism.
        Member::query()->latest('joined_on')->first()->update([
            'status' => MemberStatus::Blacklisted,
            'blacklist_reason' => 'Repeated aggressive behaviour towards staff; membership terminated by management.',
        ]);
    }
}
