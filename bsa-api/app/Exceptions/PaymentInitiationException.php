<?php

namespace App\Exceptions;

use Exception;

class PaymentInitiationException extends Exception
{
    public function __construct(string $detail = 'Payment initiation failed.')
    {
        parent::__construct($detail);
    }
}
