<?php

namespace App\Models;

use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AccessCredential extends Model
{
    use HasUuids, LogsActivity;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => CredentialType::class,
            'deposit_amount' => 'integer',
            'deposit_refunded_at' => 'datetime',
            'status' => CredentialStatus::class,
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'revoke_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CredentialStatus::Active);
    }

    /**
     * Raw identifiers are never stored — only their sha256.
     */
    public static function hashIdentifier(string $raw): string
    {
        return hash('sha256', trim($raw));
    }
}
