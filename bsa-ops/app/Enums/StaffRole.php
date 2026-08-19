<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StaffRole: string implements HasLabel, HasColor
{
    case SuperAdmin = 'super_admin';
    case Manager = 'manager';
    case Accountant = 'accountant';
    case FrontDesk = 'front_desk';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Manager => 'Manager',
            self::Accountant => 'Accountant',
            self::FrontDesk => 'Front Desk',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Manager => 'warning',
            self::Accountant => 'info',
            self::FrontDesk => 'gray',
        };
    }
}
