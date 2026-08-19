<?php

namespace App\Models;

use App\Enums\CheckInSource;
use App\Enums\DenialReason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'source' => CheckInSource::class,
            'was_allowed' => 'boolean',
            'denial_reason' => DenialReason::class,
            'session_consumed' => 'boolean',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(MemberSubscription::class, 'member_subscription_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(AccessDevice::class, 'access_device_id');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('checked_in_at', today());
    }

    public function scopeAllowed(Builder $query): Builder
    {
        return $query->where('was_allowed', true);
    }
}
