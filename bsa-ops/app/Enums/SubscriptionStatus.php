<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionStatus: string implements HasLabel, HasColor
{
    case Pending = 'pending';
    case Active = 'active';
    case Frozen = 'frozen';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Active => 'Active',
            self::Frozen => 'Frozen',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Active => 'success',
            self::Frozen => 'info',
            self::Expired => 'warning',
            self::Cancelled => 'danger',
        };
    }
}
