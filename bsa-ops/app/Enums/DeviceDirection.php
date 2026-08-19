<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DeviceDirection: string implements HasLabel
{
    case Entry = 'entry';
    case Exit = 'exit';

    public function getLabel(): string
    {
        return match ($this) {
            self::Entry => 'Entry',
            self::Exit => 'Exit',
        };
    }

}
