<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'features'   => 'array',
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    /**
     * When a Cloudinary asset is uploaded via Filament, auto-generate the
     * optimized delivery URL (f_auto,q_auto). Mirrors ProductImage.
     */
    protected static function booted(): void
    {
        static::saving(function (Facility $facility) {
            if ($facility->cloudinary_id) {
                $cloud = config('cloudinary.cloud_name', env('CLOUDINARY_CLOUD_NAME', 'dhknx0eac'));
                $facility->image_url = "https://res.cloudinary.com/{$cloud}/image/upload/f_auto,q_auto/{$facility->cloudinary_id}";
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
