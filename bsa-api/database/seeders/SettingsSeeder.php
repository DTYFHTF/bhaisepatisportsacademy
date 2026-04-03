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
                'store_tagline'    => 'Train Harder. Move Faster. Grow Stronger.',
                'contact_email'    => 'info@bsa.abinmaharjan.com.np',
                'contact_phone'    => '9821357118',
                'contact_address'  => 'Bhaisepati, Lalitpur, Nepal',
                'store_lat'        => 27.6617,
                'store_lng'        => 85.3123,
                'google_maps_url'  => 'https://maps.app.goo.gl/ZzmXJ5rDDKihfaeu7',
                'instagram_url'    => 'https://www.instagram.com/bhaisepatisportsacademy/',
                'facebook_url'     => null,
                'whatsapp_number'  => '9821357118',
                'delivery_tagline' => 'Free delivery on sports gear orders over NPR 5,000.',
                'return_tagline'   => 'Satisfaction guaranteed on every visit.',
            ]
        );
    }
}
