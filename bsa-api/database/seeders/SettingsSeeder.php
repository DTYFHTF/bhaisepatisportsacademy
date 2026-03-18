<?php

namespace Database\Seeders;

use App\Models\SiteSettings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        SiteSettings::updateOrCreate(
            ['id' => 1],
            [
                'store_name'       => 'Bhaisepati Sports Academy',
                'store_tagline'    => 'Train harder. Move faster. Grow stronger.',
                'contact_email'    => 'info@bsa.example.com',
                'contact_phone'    => '',
                'contact_address'  => 'Bhaisepati, Kathmandu, Nepal',
                'store_lat'        => 27.7172,
                'store_lng'        => 85.3240,
                'instagram_url'    => 'https://instagram.com/bsa.example.com',
                'facebook_url'     => null,
                'whatsapp_number'  => '9821357118',
                'delivery_tagline' => 'Free delivery on product orders over NPR 5,000.',
                'return_tagline'   => 'Satisfaction guaranteed on every visit.',
            ]
        );
    }
}
