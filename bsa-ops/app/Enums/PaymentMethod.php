<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Cash = 'cash';
    case Esewa = 'esewa';
    case Khalti = 'khalti';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case Card = 'card';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Esewa => 'eSewa',
            self::Khalti => 'Khalti',
            self::BankTransfer => 'Bank transfer',
            self::Cheque => 'Cheque',
            self::Card => 'Card',
        };
    }

}
