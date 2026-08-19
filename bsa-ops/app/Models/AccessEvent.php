<?php

namespace App\Models;

use App\Enums\AccessDecision;
use App\Enums\DenialReason;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only raw audit log of hardware access attempts.
 */
class AccessEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'decision' => AccessDecision::class,
            'deny_reason' => DenialReason::class,
            'occurred_at' => 'datetime',
            'raw_payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AccessEvent $event) {
            $event->created_at ??= now();
        });
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(AccessDevice::class, 'access_device_id');
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(AccessCredential::class, 'access_credential_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
