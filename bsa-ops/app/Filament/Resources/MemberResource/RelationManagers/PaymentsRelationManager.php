<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('received_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')->copyable(),
                Tables\Columns\TextColumn::make('invoice.invoice_number'),
                Tables\Columns\TextColumn::make('amount')->formatStateUsing(fn (int $state) => Money::npr($state)),
                Tables\Columns\TextColumn::make('method')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('received_at')->dateTime('j M Y H:i'),
                Tables\Columns\TextColumn::make('receivedBy.name')->label('By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
