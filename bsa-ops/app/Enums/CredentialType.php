<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CredentialType: string implements HasLabel
{
    case RfidCard = 'rfid_card';
    case QrCode = 'qr_code';
    case Fingerprint = 'fingerprint';

    public function getLabel(): string
    {
        return match ($this) {
            self::RfidCard => 'RFID card',
            self::QrCode => 'QR code',
            self::Fingerprint => 'Fingerprint',
        };
    }

}
