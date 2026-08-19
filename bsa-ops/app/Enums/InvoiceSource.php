<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InvoiceSource: string implements HasLabel, HasColor
{
    case Membership = 'membership';
    case Pos = 'pos';

    public function getLabel(): string
    {
        return match ($this) {
            self::Membership => 'Membership',
            self::Pos => 'POS',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Membership => 'info',
            self::Pos => 'warning',
        };
    }
}
