<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KitchenItem extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price'      => 'integer',
            'sort_order' => 'integer',
            'is_popular' => 'boolean',
            'is_active'  => 'boolean',
        ];
    }

    /**
     * Auto-generate the optimized Cloudinary delivery URL from an uploaded
     * asset id (mirrors ProductImage / Program / Facility).
     */
    protected static function booted(): void
    {
        static::saving(function (KitchenItem $item) {
            if ($item->cloudinary_id) {
                $cloud = config('cloudinary.cloud_name', env('CLOUDINARY_CLOUD_NAME', 'dhknx0eac'));
                $item->image_url = "https://res.cloudinary.com/{$cloud}/image/upload/f_auto,q_auto/{$item->cloudinary_id}";
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
