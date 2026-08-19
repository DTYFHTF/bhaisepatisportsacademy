<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccessDecision: string implements HasLabel, HasColor
{
    case Allowed = 'allowed';
    case Denied = 'denied';

    public function getLabel(): string
    {
        return match ($this) {
            self::Allowed => 'Allowed',
            self::Denied => 'Denied',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Allowed => 'success',
            self::Denied => 'danger',
        };
    }
}
