<?php

namespace App\Filament\Resources\TrialBookingResource\Pages;

use App\Filament\Resources\TrialBookingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Booking;

class CreateTrialBooking extends CreateRecord
{
    protected static string $resource = TrialBookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'trial';
        if (!isset($data['ref'])) {
            $data['ref'] = Booking::generateRef();
        }
        return $data;
    }
}
