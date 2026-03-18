<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case KHALTI = 'KHALTI';
    case ESEWA  = 'ESEWA';
    case COD    = 'COD';
}
