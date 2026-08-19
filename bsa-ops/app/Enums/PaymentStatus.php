<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasLabel, HasColor
{
    case Completed = 'completed';
    case PendingVerification = 'pending_verification';
    case Bounced = 'bounced';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::PendingVerification => 'Pending verification',
            self::Bounced => 'Bounced',
            self::Refunded => 'Refunded',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Completed => 'success',
            self::PendingVerification => 'warning',
            self::Bounced => 'danger',
            self::Refunded => 'gray',
        };
    }
}
