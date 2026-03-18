<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING    = 'PENDING';
    case CONFIRMED  = 'CONFIRMED';
    case PACKED     = 'PACKED';
    case DISPATCHED = 'DISPATCHED';
    case DELIVERED  = 'DELIVERED';
    case CANCELLED  = 'CANCELLED';
}
