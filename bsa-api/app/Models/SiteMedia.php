<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SiteMedia extends Model
{
    use HasUuids;

    protected $table = 'site_media';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * Auto-generate the optimized Cloudinary delivery URL from an uploaded
     * asset id (mirrors Program / Facility / Coach / KitchenItem).
     */
    protected static function booted(): void
    {
        static::saving(function (SiteMedia $media) {
            if ($media->cloudinary_id) {
                $cloud = config('cloudinary.cloud_name', env('CLOUDINARY_CLOUD_NAME', 'dhknx0eac'));
                $media->url = "https://res.cloudinary.com/{$cloud}/image/upload/f_auto,q_auto/{$media->cloudinary_id}";
            }
        });
    }
}
