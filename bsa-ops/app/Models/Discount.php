<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->valid_from && today()->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && today()->gt($this->valid_until)) {
            return false;
        }

        return $this->max_uses === null || $this->used_count < $this->max_uses;
    }

    /**
     * Discount amount in paisa for a given base amount.
     * Percent discounts are stored in basis points (1000 = 10%).
     */
    public function apply(int $amount): int
    {
        return match ($this->type) {
            DiscountType::Percent => intdiv($amount * $this->value, 10000),
            DiscountType::Fixed => min($this->value, $amount),
        };
    }
}
