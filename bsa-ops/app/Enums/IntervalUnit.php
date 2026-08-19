<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IntervalUnit: string implements HasLabel
{
    case Days = 'days';
    case Months = 'months';

    public function getLabel(): string
    {
        return match ($this) {
            self::Days => 'Days',
            self::Months => 'Months',
        };
    }

}
