<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('label')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('e.g. 50ml, 100ml, Standard'),
                TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true),
            ]),
            Grid::make(3)->schema([
                TextInput::make('price_override')
                    ->numeric()
                    ->prefix('NPR')
                    ->helperText('Leave empty to use product price'),
                TextInput::make('stock')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->minValue(0),
                TextInput::make('reserved_stock')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->badge()
                    ->sortable(),
                TextColumn::make('sku')
                    ->copyable(),
                TextColumn::make('price_override')
                    ->formatStateUsing(fn (?int $state) => $state ? 'NPR ' . number_format($state / 100) : '-')
                    ->label('Price Override'),
                TextColumn::make('stock')
                    ->sortable(),
                TextColumn::make('reserved_stock')
                    ->label('Reserved'),
                TextColumn::make('available')
                    ->getStateUsing(fn ($record) => $record->stock - $record->reserved_stock)
                    ->color(fn ($state): string => $state <= 3 ? 'danger' : 'success')
                    ->weight('bold'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
