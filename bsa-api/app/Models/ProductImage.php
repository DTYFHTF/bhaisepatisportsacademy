<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasUuids;

    protected $guarded = [];

    /**
     * If a cloudinary_id is saved without a URL, auto-generate the delivery URL.
     * Format: f_auto,q_auto for browser-optimal format + quality.
     */
    protected static function booted(): void
    {
        static::saving(function (ProductImage $image) {
            if ($image->cloudinary_id) {
                $cloud = config('cloudinary.cloud_name', env('CLOUDINARY_CLOUD_NAME', 'dhknx0eac'));
                $image->url = "https://res.cloudinary.com/{$cloud}/image/upload/f_auto,q_auto/{$image->cloudinary_id}";
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
