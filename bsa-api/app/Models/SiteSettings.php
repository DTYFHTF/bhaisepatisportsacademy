<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSettings extends Model
{
    protected $guarded = [];

    protected $casts = [
        'store_lat' => 'float',
        'store_lng' => 'float',
    ];

    /** Always returns the single settings row, creating it if missing. */
    public static function current(): static
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'store_name'       => 'Bhaisepati Sports Academy',
                'store_tagline'    => 'Train harder. Move faster. Grow stronger.',
                'contact_email'    => 'info@bsa.example.com',
                'contact_phone'    => '',
                'contact_address'  => 'Bhaisepati, Kathmandu, Nepal',
                'store_lat'        => 27.7172,
                'store_lng'        => 85.3240,
                'google_maps_url'  => null,
                'instagram_url'    => 'https://instagram.com/bsa.example.com',
                'facebook_url'     => null,
                'whatsapp_number'  => '9821357118',
                'delivery_tagline' => 'Free delivery on product orders over NPR 5,000.',
                'return_tagline'   => 'Satisfaction guaranteed on every visit.',
            ]
        );
    }

    /** Public fields exposed via /api/settings */
    public function toPublicArray(): array
    {
        return [
            'store_name'       => $this->store_name,
            'store_tagline'    => $this->store_tagline,
            'logo_url'         => $this->logo_path ? Storage::url($this->logo_path) : null,
            'icon_url'         => $this->icon_path ? Storage::url($this->icon_path) : null,
            'contact_email'    => $this->contact_email,
            'contact_phone'    => $this->contact_phone,
            'contact_address'  => $this->contact_address,
            'store_lat'        => $this->store_lat,
            'store_lng'        => $this->store_lng,
            'google_maps_url'  => $this->google_maps_url,
            'instagram_url'    => $this->instagram_url,
            'facebook_url'     => $this->facebook_url,
            'whatsapp_number'  => $this->whatsapp_number,
            'delivery_tagline' => $this->delivery_tagline,
            'return_tagline'   => $this->return_tagline,
        ];
    }
}
