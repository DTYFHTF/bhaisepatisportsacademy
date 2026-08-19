<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CheckInSource: string implements HasLabel, HasColor
{
    case FrontDesk = 'front_desk';
    case Qr = 'qr';
    case DoorController = 'door_controller';

    public function getLabel(): string
    {
        return match ($this) {
            self::FrontDesk => 'Front desk',
            self::Qr => 'QR',
            self::DoorController => 'Door controller',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FrontDesk => 'info',
            self::Qr => 'warning',
            self::DoorController => 'success',
        };
    }
}
