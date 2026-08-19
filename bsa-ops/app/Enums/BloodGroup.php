<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BloodGroup: string implements HasLabel
{
    case APos = 'a_pos';
    case ANeg = 'a_neg';
    case BPos = 'b_pos';
    case BNeg = 'b_neg';
    case OPos = 'o_pos';
    case ONeg = 'o_neg';
    case ABPos = 'ab_pos';
    case ABNeg = 'ab_neg';

    public function getLabel(): string
    {
        return match ($this) {
            self::APos => 'A+',
            self::ANeg => 'A−',
            self::BPos => 'B+',
            self::BNeg => 'B−',
            self::OPos => 'O+',
            self::ONeg => 'O−',
            self::ABPos => 'AB+',
            self::ABNeg => 'AB−',
        };
    }

}
