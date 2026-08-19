<?php

namespace App\Filament\Resources\AccessDeviceResource\Pages;

use App\Filament\Resources\AccessDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAccessDevices extends ManageRecords
{
    protected static string $resource = AccessDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }
}
