<?php

namespace App\Filament\Resources;

use App\Enums\AccessDecision;
use App\Filament\Resources\AccessEventResource\Pages;
use App\Models\AccessDevice;
use App\Models\AccessEvent;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccessEventResource extends Resource
{
    protected static ?string $model = AccessEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Access';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'access event';

    public static function canCreate(): bool
    {
        return false; // append-only hardware log
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('occurred_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['member', 'device', 'department']))
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')->dateTime('j M H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('device.name')
                    ->description(fn (AccessEvent $record) => $record->device_uid),
                Tables\Columns\TextColumn::make('member.first_name')
                    ->label('Member')
                    ->formatStateUsing(fn (AccessEvent $record) => $record->member?->full_name)
                    ->placeholder('Unknown'),
                Tables\Columns\TextColumn::make('department.name')->placeholder('—'),
                Tables\Columns\TextColumn::make('decision')->badge(),
                Tables\Columns\TextColumn::make('deny_reason')->badge()->color('danger')->placeholder('—'),
                Tables\Columns\TextColumn::make('credential_hint')
                    ->label('Credential hash')
                    ->limit(12)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('access_device_id')
                    ->label('Device')
                    ->options(AccessDevice::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('decision')->options(AccessDecision::class),
            ])
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessEvents::route('/'),
        ];
    }
}
