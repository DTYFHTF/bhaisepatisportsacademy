<?php

namespace App\Filament\Resources;

use App\Enums\DeviceDirection;
use App\Enums\DeviceProtocol;
use App\Enums\DeviceType;
use App\Filament\Resources\AccessDeviceResource\Pages;
use App\Models\AccessDevice;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AccessDeviceResource extends Resource
{
    protected static ?string $model = AccessDevice::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Access';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'device';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('device_uid')
                    ->label('Device UID / serial number')
                    ->helperText('ZKTeco: the serial number on the sticker — it is how the device identifies itself.')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('type')->options(DeviceType::class)->required()->native(false),
                Forms\Components\Select::make('protocol')
                    ->options(DeviceProtocol::class)
                    ->default(DeviceProtocol::Native)
                    ->required()
                    ->native(false)
                    ->helperText('ZKTeco ADMS = the device decides locally; we sync its user list.'),
                Forms\Components\Select::make('department_id')
                    ->label('Department')
                    ->options(Department::active()->pluck('name', 'id'))
                    ->helperText('The door this device controls. Kiosks may be unassigned.')
                    ->native(false),
                Forms\Components\Select::make('direction')
                    ->options(DeviceDirection::class)
                    ->default(DeviceDirection::Entry)
                    ->native(false),
                Forms\Components\TextInput::make('ip_address')->label('IP address'),
                Forms\Components\TextInput::make('firmware'),
                Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                Forms\Components\Textarea::make('notes')->columnSpan(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (AccessDevice $record) => $record->device_uid)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('protocol')->badge(),
                Tables\Columns\TextColumn::make('department.name')->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('direction')->badge()->color('info'),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->dateTime('j M Y H:i')
                    ->placeholder('Never')
                    ->color(fn (AccessDevice $record) => $record->last_seen_at === null ? 'gray'
                        : ($record->last_seen_at->lt(now()->subMinutes(10)) ? 'danger' : 'success')),
                Tables\Columns\TextColumn::make('ip_address')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('firmware')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('syncNow')
                    ->label('Sync users')
                    ->icon('heroicon-m-arrow-path')
                    ->color('info')
                    ->visible(fn (AccessDevice $record) => $record->protocol === DeviceProtocol::ZktecoAdms)
                    ->requiresConfirmation()
                    ->modalDescription('Re-checks every member against this door and queues enrol/revoke commands. The device picks them up on its next poll.')
                    ->action(function (AccessDevice $record) {
                        $queued = app(\App\Services\ZktecoSyncService::class)->syncDevice($record);

                        Notification::make()->success()
                            ->title("Queued {$queued} commands")
                            ->body($queued === 0 ? 'Device already in sync.' : 'The door applies them within a minute.')
                            ->send();
                    }),
                Tables\Actions\Action::make('generateToken')
                    ->label('API token')
                    ->icon('heroicon-m-key')
                    ->color('warning')
                    ->visible(fn (AccessDevice $record) => $record->protocol === DeviceProtocol::Native)
                    ->requiresConfirmation()
                    ->modalDescription('Generates a fresh API token for this device and revokes all previous ones. The token is shown ONCE — copy it into the device configuration.')
                    ->action(function (AccessDevice $record) {
                        $record->tokens()->delete();
                        $token = $record->createToken('device', ['device:verify'])->plainTextToken;

                        Notification::make()
                            ->title('Device token (copy now — shown once)')
                            ->body($token)
                            ->persistent()
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make()->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAccessDevices::route('/'),
        ];
    }
}
