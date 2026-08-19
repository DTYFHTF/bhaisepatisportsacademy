<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'monthly_budget' => 'integer',
            'capacity' => 'integer',
            'is_access_controlled' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function membershipPlans(): BelongsToMany
    {
        return $this->belongsToMany(MembershipPlan::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Departments whose doors are gated by eligibility (Gym, Pool…) —
     * excludes pure cost centers like the Kitchen and Pro Shop.
     */
    public function scopeAccessControlled(Builder $query): Builder
    {
        return $query->where('is_access_controlled', true);
    }
}
