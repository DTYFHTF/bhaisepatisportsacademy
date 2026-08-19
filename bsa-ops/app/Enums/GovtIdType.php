<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GovtIdType: string implements HasLabel
{
    case Citizenship = 'citizenship';
    case NationalId = 'national_id';
    case Passport = 'passport';
    case DrivingLicense = 'driving_license';

    public function getLabel(): string
    {
        return match ($this) {
            self::Citizenship => 'Citizenship',
            self::NationalId => 'National ID',
            self::Passport => 'Passport',
            self::DrivingLicense => 'Driving License',
        };
    }

}
