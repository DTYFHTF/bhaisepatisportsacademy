<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReferralSource: string implements HasLabel
{
    case WalkIn = 'walk_in';
    case Friend = 'friend';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case Website = 'website';
    case Event = 'event';
    case Corporate = 'corporate';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::WalkIn => 'Walk-in',
            self::Friend => 'Friend',
            self::Facebook => 'Facebook',
            self::Instagram => 'Instagram',
            self::Website => 'Website',
            self::Event => 'Event',
            self::Corporate => 'Corporate',
            self::Other => 'Other',
        };
    }

}
