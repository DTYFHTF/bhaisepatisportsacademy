<?php

namespace App\Models;

use App\Enums\Category;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tags'             => 'array',
            'category'         => Category::class,
            'is_active'        => 'boolean',
            'price'            => 'integer',
            'compare_at_price' => 'integer',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function restockAlerts(): HasMany
    {
        return $this->hasMany(RestockAlert::class);
    }

    public function pairedWith(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_pairs', 'product_id', 'paired_with_id');
    }
}
