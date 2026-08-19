<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockMovementType: string implements HasLabel, HasColor
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Consumption = 'consumption';
    case Adjustment = 'adjustment';
    case WriteOff = 'write_off';

    public function getLabel(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Sale => 'Sale',
            self::Consumption => 'Consumption',
            self::Adjustment => 'Adjustment',
            self::WriteOff => 'Write-off',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Purchase => 'success',
            self::Sale => 'info',
            self::Consumption => 'warning',
            self::Adjustment => 'gray',
            self::WriteOff => 'danger',
        };
    }
}
