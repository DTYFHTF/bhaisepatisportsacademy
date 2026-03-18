<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    public function __construct(public readonly string $variantId)
    {
        parent::__construct("Variant {$variantId} is out of stock.");
    }
}
