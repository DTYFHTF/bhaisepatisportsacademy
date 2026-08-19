<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PlanType: string implements HasLabel, HasColor
{
    case TimeBased = 'time_based';
    case SessionPack = 'session_pack';

    public function getLabel(): string
    {
        return match ($this) {
            self::TimeBased => 'Time-based',
            self::SessionPack => 'Session pack',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::TimeBased => 'info',
            self::SessionPack => 'warning',
        };
    }
}
