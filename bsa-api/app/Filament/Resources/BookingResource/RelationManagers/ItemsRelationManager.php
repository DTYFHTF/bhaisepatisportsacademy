<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Booking Items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                Select::make('service_id')
                    ->relationship('service', 'name')
                    ->required(),
                TextInput::make('service_name')
                    ->required()
                    ->maxLength(255),
            ]),
            Grid::make(2)->schema([
                TextInput::make('duration')
                    ->required()
                    ->numeric()
                    ->suffix('min'),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->prefix('NPR')
                    ->helperText('In paisa'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service_name'),
                TextColumn::make('duration')->suffix(' min'),
                TextColumn::make('unit_price')
                    ->formatStateUsing(fn (int $state) => 'NPR ' . number_format($state / 100, 0)),
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
