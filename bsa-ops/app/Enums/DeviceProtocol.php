<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DeviceProtocol: string implements HasLabel, HasColor
{
    /** Our own JSON API — the controller asks us to decide, in real time. */
    case Native = 'native';

    /** ZKTeco PUSH/ADMS — the device decides locally, we sync its user list. */
    case ZktecoAdms = 'zkteco_adms';

    public function getLabel(): string
    {
        return match ($this) {
            self::Native => 'BSA native (real-time)',
            self::ZktecoAdms => 'ZKTeco ADMS (push)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Native => 'success',
            self::ZktecoAdms => 'info',
        };
    }
}
