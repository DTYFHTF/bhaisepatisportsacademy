<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MemberSubscription extends Model
{
    use HasUuids, LogsActivity;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'admission_fee' => 'integer',
            'discount_amount' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'sessions_total' => 'integer',
            'sessions_remaining' => 'integer',
            'status' => SubscriptionStatus::class,
            'activated_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'starts_on', 'ends_on', 'sessions_remaining', 'price'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ---- Relationships ----

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(MemberSubscription::class, 'renewed_from_id');
    }

    public function freezes(): HasMany
    {
        return $this->hasMany(SubscriptionFreeze::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ---- Scopes ----

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::Active);
    }

    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->where('status', SubscriptionStatus::Active)
            ->whereBetween('ends_on', [today(), today()->addDays($days)]);
    }

    // ---- Helpers ----

    public function coversDepartment(Department $department): bool
    {
        return $this->plan->departments->contains('id', $department->id);
    }

    public function isCurrentlyFrozen(): bool
    {
        return $this->status === SubscriptionStatus::Frozen;
    }

    public function totalFrozenDays(): int
    {
        return (int) $this->freezes()->sum('days_count');
    }

    /**
     * Amount actually charged for this subscription (paisa).
     */
    public function netPrice(): int
    {
        return max(0, $this->price + $this->admission_fee - $this->discount_amount);
    }
}
