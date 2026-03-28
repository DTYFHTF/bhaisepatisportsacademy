<?php

namespace App\Filament\Resources\ScheduleSlotResource\Pages;

use App\Filament\Resources\ScheduleSlotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduleSlots extends ListRecords
{
    protected static string $resource = ScheduleSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
