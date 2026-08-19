<?php

namespace App\Filament\Resources;

use App\Enums\CheckInSource;
use App\Filament\Resources\CheckInResource\Pages;
use App\Models\CheckIn;
use App\Models\Department;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CheckInResource extends Resource
{
    protected static ?string $model = CheckIn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-end-on-rectangle';

    protected static ?string $navigationGroup = 'Access';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'check-in';

    public static function canCreate(): bool
    {
        return false; // recorded by kiosk / devices / member actions
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('checked_in_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['member', 'department']))
            ->columns([
                Tables\Columns\TextColumn::make('checked_in_at')->dateTime('j M H:i')->sortable(),
                Tables\Columns\TextColumn::make('member.first_name')
                    ->label('Member')
                    ->formatStateUsing(fn (CheckIn $record) => $record->member->full_name)
                    ->description(fn (CheckIn $record) => $record->member->member_code)
                    ->searchable(['first_name', 'last_name', 'member_code'])
                    ->url(fn (CheckIn $record) => MemberResource::getUrl('view', ['record' => $record->member])),
                Tables\Columns\TextColumn::make('department.name'),
                Tables\Columns\TextColumn::make('source')->badge(),
                Tables\Columns\IconColumn::make('was_allowed')->label('Allowed')->boolean(),
                Tables\Columns\TextColumn::make('denial_reason')->badge()->color('danger')->placeholder('—'),
                Tables\Columns\IconColumn::make('session_consumed')->label('Session')->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('checkedInBy.name')->label('Staff')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('source')->options(CheckInSource::class),
                Tables\Filters\TernaryFilter::make('was_allowed')->label('Allowed'),
                Tables\Filters\Filter::make('today')
                    ->query(fn (Builder $query) => $query->whereDate('checked_in_at', today()))
                    ->default(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckIns::route('/'),
        ];
    }
}
