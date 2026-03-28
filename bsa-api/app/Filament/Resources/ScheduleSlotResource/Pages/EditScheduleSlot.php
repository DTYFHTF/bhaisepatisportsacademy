<?php

namespace App\Filament\Resources\ScheduleSlotResource\Pages;

use App\Filament\Resources\ScheduleSlotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduleSlot extends EditRecord
{
    protected static string $resource = ScheduleSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
