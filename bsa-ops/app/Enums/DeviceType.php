<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DeviceType: string implements HasLabel
{
    case DoorController = 'door_controller';
    case Turnstile = 'turnstile';
    case Kiosk = 'kiosk';

    public function getLabel(): string
    {
        return match ($this) {
            self::DoorController => 'Door controller',
            self::Turnstile => 'Turnstile',
            self::Kiosk => 'Kiosk',
        };
    }

}
