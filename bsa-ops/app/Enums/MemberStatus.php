<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MemberStatus: string implements HasLabel, HasColor
{
    case Active = 'active';
    case Frozen = 'frozen';
    case Expired = 'expired';
    case Blacklisted = 'blacklisted';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Frozen => 'Frozen',
            self::Expired => 'Expired',
            self::Blacklisted => 'Blacklisted',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Frozen => 'info',
            self::Expired => 'warning',
            self::Blacklisted => 'danger',
        };
    }
}
