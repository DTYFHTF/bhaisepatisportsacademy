<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    use HasUuids;

    /**
     * Laravel would pluralise this to "coachs"; the table is "coaches".
     */
    protected $table = 'coaches';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'specialties'      => 'array',
            'experience_years' => 'integer',
            'sort_order'       => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    /**
     * Auto-generate the optimized Cloudinary delivery URL from an uploaded
     * asset id (mirrors ProductImage / Program / Facility / KitchenItem).
     */
    protected static function booted(): void
    {
        static::saving(function (Coach $coach) {
            if ($coach->cloudinary_id) {
                $cloud = config('cloudinary.cloud_name', env('CLOUDINARY_CLOUD_NAME', 'dhknx0eac'));
                $coach->image_url = "https://res.cloudinary.com/{$cloud}/image/upload/f_auto,q_auto/{$coach->cloudinary_id}";
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
