<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'features'         => 'array',
            'price'            => 'integer',
            'sessions_per_week'=> 'integer',
            'sort_order'       => 'integer',
            'is_popular'       => 'boolean',
            'is_active'        => 'boolean',
        ];
    }

    /**
     * When a Cloudinary asset is uploaded via Filament, auto-generate the
     * optimized delivery URL (f_auto,q_auto). Mirrors ProductImage.
     */
    protected static function booted(): void
    {
        static::saving(function (Program $program) {
            if ($program->cloudinary_id) {
                $cloud = config('cloudinary.cloud_name', env('CLOUDINARY_CLOUD_NAME', 'dhknx0eac'));
                $program->image_url = "https://res.cloudinary.com/{$cloud}/image/upload/f_auto,q_auto/{$program->cloudinary_id}";
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
