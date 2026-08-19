<?php

namespace App\Models;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\GovtIdType;
use App\Enums\MemberStatus;
use App\Enums\ReferralSource;
use App\Enums\SubscriptionStatus;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

/**
 * Authenticatable + HasApiTokens so the member portal API can issue
 * Sanctum tokens to members (same pattern as AccessDevice).
 */
class Member extends Model implements AuthenticatableContract
{
    use HasUuids, SoftDeletes, HasApiTokens, Authenticatable;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender' => Gender::class,
            'blood_group' => BloodGroup::class,
            'ward_no' => 'integer',
            'govt_id_type' => GovtIdType::class,
            'referral_source' => ReferralSource::class,
            'marketing_consent' => 'boolean',
            'status' => MemberStatus::class,
            'joined_on' => 'date',
        ];
    }

    // ---- Relationships ----

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(AccessCredential::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'referred_by_member_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ---- Accessors ----

    public function getFullNameAttribute(): string
    {
        return collect([$this->first_name, $this->middle_name, $this->last_name])
            ->filter()
            ->implode(' ');
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getIsMinorAttribute(): bool
    {
        return $this->age !== null && $this->age < 18;
    }

    // ---- Scopes ----

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', MemberStatus::Active);
    }

    public function scopeWithDues(Builder $query): Builder
    {
        return $query->whereHas('invoices', fn (Builder $q) => $q->where('balance', '>', 0)->whereNull('voided_at'));
    }

    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->whereHas('subscriptions', fn (Builder $q) => $q
            ->where('status', SubscriptionStatus::Active)
            ->whereBetween('ends_on', [today(), today()->addDays($days)]));
    }

    // ---- Helpers ----

    /**
     * Numeric user ID used by ZKTeco hardware, which only accepts integer
     * PINs. Derived from the unique numeric part of the member code
     * (BSA-00013 → 13), so it is stable and needs no extra column.
     */
    public function devicePin(): int
    {
        return (int) preg_replace('/\D/', '', $this->member_code);
    }

    /**
     * Sum of open invoice balances, in paisa.
     */
    public function outstandingBalance(): int
    {
        return (int) $this->invoices()
            ->whereNull('voided_at')
            ->sum('balance');
    }
}
