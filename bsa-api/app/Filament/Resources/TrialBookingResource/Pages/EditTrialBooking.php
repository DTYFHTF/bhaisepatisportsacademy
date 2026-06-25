<?php

namespace App\Filament\Resources\TrialBookingResource\Pages;

use App\Filament\Resources\TrialBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrialBooking extends EditRecord
{
    protected static string $resource = TrialBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}