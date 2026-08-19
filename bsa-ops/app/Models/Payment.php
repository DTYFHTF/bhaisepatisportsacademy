<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasUuids, LogsActivity;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'gateway_payload' => 'array',
            'cheque_date' => 'date',
            'received_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ---- Relationships ----

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ---- Scopes ----

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Completed);
    }

    public function scopePendingVerification(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::PendingVerification);
    }
}
