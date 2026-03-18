<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING   = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case COMPLETED = 'COMPLETED';
    case NO_SHOW   = 'NO_SHOW';
    case CANCELLED = 'CANCELLED';
}
