<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductCategory: string implements HasLabel, HasColor
{
    case Shop = 'shop';
    case Kitchen = 'kitchen';
    case Consumable = 'consumable';

    public function getLabel(): string
    {
        return match ($this) {
            self::Shop => 'Pro shop',
            self::Kitchen => 'Kitchen',
            self::Consumable => 'Consumable',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Shop => 'info',
            self::Kitchen => 'warning',
            self::Consumable => 'gray',
        };
    }
}
