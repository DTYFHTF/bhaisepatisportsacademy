<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Services\SmsService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('change_status')
                ->label('Change Status')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->form([
                    Select::make('status')
                        ->options(collect(OrderStatus::cases())->mapWithKeys(
                            fn ($s) => [$s->value => $s->value]
                        )->toArray())
                        ->required()
                        ->native(false)
                        ->default(fn () => $this->record->status instanceof \BackedEnum
                            ? $this->record->status->value
                            : $this->record->status),
                    Textarea::make('note')
                        ->placeholder('Optional note for this status change'),
                ])
                ->action(function (array $data) {
                    $this->record->update(['status' => $data['status']]);
                    $this->record->statusHistory()->create([
                        'status'     => $data['status'],
                        'note'       => $data['note'] ?? null,
                        'changed_by' => auth()->user()->email,
                        'changed_at' => now(),
                    ]);
                    app(SmsService::class)->sendStatusUpdate($this->record);
                    Notification::make()->title('Status updated')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('assign_courier')
                ->label('Assign Courier')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->form([
                    Select::make('courier')
                        ->options([
                            'PATHAO' => 'Pathao Parcel',
                            'NCM'    => 'Nepal Can Move',
                            'DHULO'  => 'Dhulo',
                            'OTHER'  => 'Other',
                        ])
                        ->required()
                        ->native(false),
                    TextInput::make('courier_tracking_id')
                        ->label('Tracking Number')
                        ->placeholder('e.g. NCM-123456'),
                    TextInput::make('courier_tracking_url')
                        ->label('Tracking URL (optional)')
                        ->url()
                        ->placeholder('https://...'),
                ])
                ->fillForm(fn () => [
                    'courier'              => $this->record->courier,
                    'courier_tracking_id'  => $this->record->courier_tracking_id,
                    'courier_tracking_url' => $this->record->courier_tracking_url,
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'courier'              => $data['courier'],
                        'courier_tracking_id'  => $data['courier_tracking_id'] ?? null,
                        'courier_tracking_url' => $data['courier_tracking_url'] ?? null,
                    ]);
                    Notification::make()->title('Courier assigned')->success()->send();
                    $this->refreshFormData(['courier', 'courier_tracking_id', 'courier_tracking_url']);
                }),

            Action::make('cancel_order')
                ->label('Cancel Order')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Cancel this order?')
                ->modalDescription('This will cancel the order and cannot be undone.')
                ->visible(fn () => in_array(
                    $this->record->status instanceof \BackedEnum ? $this->record->status->value : $this->record->status,
                    ['PENDING_PAYMENT', 'CONFIRMED', 'PROCESSING']
                ))
                ->action(function () {
                    $this->record->update(['status' => 'CANCELLED']);
                    $this->record->statusHistory()->create([
                        'status'     => 'CANCELLED',
                        'note'       => 'Cancelled manually from admin panel.',
                        'changed_at' => now(),
                    ]);
                    Notification::make()->title('Order cancelled')->success()->send();
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}
