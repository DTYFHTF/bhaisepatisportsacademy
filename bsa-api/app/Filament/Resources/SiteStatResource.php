<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteStatResource\Pages;
use App\Models\SiteStat;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteStatResource extends Resource
{
    protected static ?string $model = SiteStat::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Site Stats';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Stat')->schema([
                Grid::make(2)->schema([
                    TextInput::make('value_label')->required()->maxLength(50)->placeholder('e.g. 200+'),
                    TextInput::make('label')->required()->maxLength(100)->placeholder('e.g. Members Trained'),
                ]),

                Grid::make(2)->schema([
                    Toggle::make('is_active')->label('Active')->default(true),
                    TextInput::make('sort_order')->numeric()->default(0),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('value_label')->sortable(),
                TextColumn::make('label')->searchable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSiteStats::route('/'),
            'create' => Pages\CreateSiteStat::route('/create'),
            'edit'   => Pages\EditSiteStat::route('/{record}/edit'),
        ];
    }
}
