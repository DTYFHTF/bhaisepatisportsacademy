<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasUuids;

    protected $guarded = [];

    /**
     * Convert a local public storage path into a full public URL on save.
     */
    protected static function booted(): void
    {
        static::saving(function (ProductImage $image) {
            if (! empty($image->url) && ! str_contains($image->url, '://')) {
                $image->url = Storage::disk('public')->url($image->url);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
