<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'org' => [
                'org_name' => 'Bhaisepati Sports Academy',
                'org_address' => 'Bhaisepati, Lalitpur-25, Nepal',
                'org_phone' => '01-5591234',
                'vat_number' => '609123456',
                'pan_number' => '609123456',
                'member_code_prefix' => 'BSA',
            ],
            'billing' => [
                'tax_rate_percent' => 13,
                'current_fiscal_year' => '2082-83',
                'fiscal_year_started_on' => '2026-07-16',
                'dues_grace_days' => 7,
                'dues_block_threshold' => 200000, // NPR 2,000 in paisa
                'receipt_footer' => 'Thank you for training with us. Fees are non-refundable once the term starts.',
            ],
        ];

        foreach ($settings as $group => $pairs) {
            foreach ($pairs as $key => $value) {
                Setting::set($key, $value, $group);
            }
        }
    }
}
