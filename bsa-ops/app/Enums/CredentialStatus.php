<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CredentialStatus: string implements HasLabel, HasColor
{
    case Active = 'active';
    case Revoked = 'revoked';
    case Lost = 'lost';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Revoked => 'Revoked',
            self::Lost => 'Lost',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Revoked => 'danger',
            self::Lost => 'warning',
        };
    }
}
