<?php

namespace App\Models;

use App\Enums\MemberStatus;
use App\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids;

    protected $guarded = [];

    /**
     * Mirrors the column defaults so a freshly created instance behaves
     * the same as one re-read from the database (a product built in
     * memory must not look inactive or untracked to the POS).
     */
    protected $attributes = [
        'unit' => 'piece',
        'cost_price' => 0,
        'is_taxable' => true,
        'price_includes_tax' => true,
        'track_stock' => true,
        'stock_on_hand' => 0,
        'reorder_level' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'category' => ProductCategory::class,
            'cost_price' => 'integer',
            'price' => 'integer',
            'member_price' => 'integer',
            'is_taxable' => 'boolean',
            'price_includes_tax' => 'boolean',
            'track_stock' => 'boolean',
            'stock_on_hand' => 'integer',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('track_stock', true)
            ->whereColumn('stock_on_hand', '<=', 'reorder_level');
    }

    public function isLowStock(): bool
    {
        return $this->track_stock && $this->stock_on_hand <= $this->reorder_level;
    }

    /**
     * The unit price this buyer pays (paisa). Active Club members get the
     * member price when one is set — "Kitchen open to all, specially for
     * Club users".
     */
    public function priceFor(?Member $member): int
    {
        if ($member && $member->status === MemberStatus::Active && $this->member_price !== null) {
            return $this->member_price;
        }

        return $this->price;
    }
}
