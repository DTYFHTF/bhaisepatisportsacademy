<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ExpenseStatus: string implements HasLabel, HasColor
{
    case Recorded = 'recorded';
    case Approved = 'approved';

    public function getLabel(): string
    {
        return match ($this) {
            self::Recorded => 'Recorded',
            self::Approved => 'Approved',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Recorded => 'warning',
            self::Approved => 'success',
        };
    }
}
