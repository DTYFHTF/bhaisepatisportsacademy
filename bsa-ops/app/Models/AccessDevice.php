<?php

namespace App\Models;

use App\Enums\DeviceDirection;
use App\Enums\DeviceProtocol;
use App\Enums\DeviceType;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * Authenticatable so Sanctum-guarded routes (and their throttle
 * middleware) can treat a device token bearer like any other principal.
 */
class AccessDevice extends Model implements AuthenticatableContract
{
    use HasUuids, HasApiTokens, Authenticatable;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => DeviceType::class,
            'protocol' => DeviceProtocol::class,
            'direction' => DeviceDirection::class,
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function accessEvents(): HasMany
    {
        return $this->hasMany(AccessEvent::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
