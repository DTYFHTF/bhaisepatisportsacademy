<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payment_method'    => PaymentMethod::class,
            'status'            => OrderStatus::class,
            'exchange_requested' => 'boolean',
            'subtotal'          => 'integer',
            'delivery_fee'      => 'integer',
            'total'             => 'integer',
            'latitude'          => 'decimal:7',
            'longitude'         => 'decimal:7',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function trackingTokens(): HasMany
    {
        return $this->hasMany(TrackingToken::class);
    }
}
