<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CheckInsRelationManager extends RelationManager
{
    protected static string $relationship = 'checkIns';

    protected static ?string $title = 'Check-ins';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('checked_in_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('checked_in_at')->dateTime('j M Y H:i'),
                Tables\Columns\TextColumn::make('department.name'),
                Tables\Columns\TextColumn::make('source')->badge(),
                Tables\Columns\IconColumn::make('was_allowed')->label('Allowed')->boolean(),
                Tables\Columns\TextColumn::make('denial_reason')->badge()->color('danger')->placeholder('—'),
                Tables\Columns\IconColumn::make('session_consumed')->label('Session used')->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
