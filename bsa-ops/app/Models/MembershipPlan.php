<?php

namespace App\Models;

use App\Enums\IntervalUnit;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'plan_type' => PlanType::class,
            'interval_unit' => IntervalUnit::class,
            'interval_count' => 'integer',
            'session_count' => 'integer',
            'validity_days' => 'integer',
            'price' => 'integer',
            'admission_fee' => 'integer',
            'is_taxable' => 'boolean',
            'price_includes_tax' => 'boolean',
            'freeze_allowance_days' => 'integer',
            'guest_passes' => 'integer',
            'is_off_peak' => 'boolean',
            'min_age' => 'integer',
            'max_age' => 'integer',
            'available_from' => 'date',
            'available_until' => 'date',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isPack(): bool
    {
        return $this->plan_type === PlanType::SessionPack;
    }

    /**
     * Length of one billing term in days (time-based plans),
     * or the validity window (session packs).
     */
    public function durationInDays(): ?int
    {
        if ($this->isPack()) {
            return $this->validity_days;
        }

        return match ($this->interval_unit) {
            IntervalUnit::Days => $this->interval_count,
            IntervalUnit::Months => $this->interval_count * 30,
            default => null,
        };
    }
}
