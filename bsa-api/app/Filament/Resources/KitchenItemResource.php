<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KitchenItemResource\Pages;
use App\Models\KitchenItem;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class KitchenItemResource extends Resource
{
    protected static ?string $model = KitchenItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Kitchen Menu';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Menu Item')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) =>
                            $set('slug', Str::slug($state ?? ''))
                        ),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                ]),

                Textarea::make('description')->rows(2)->maxLength(500),

                Grid::make(2)->schema([
                    Select::make('category')
                        ->options([
                            'pre-workout' => 'Pre-Workout',
                            'post-workout' => 'Post-Workout',
                            'snacks' => 'Snacks',
                            'drinks' => 'Drinks',
                        ])
                        ->required(),
                    TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('NPR')
                        ->helperText('In paisa (e.g. 15000 = NPR 150)'),
                ]),

                Grid::make(3)->schema([
                    Toggle::make('is_popular')->label('Popular'),
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
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category')->badge(),
                TextColumn::make('price')
                    ->formatStateUsing(fn (int $state) => 'NPR ' . number_format($state / 100, 0))
                    ->sortable(),
                IconColumn::make('is_popular')->boolean(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'pre-workout' => 'Pre-Workout',
                        'post-workout' => 'Post-Workout',
                        'snacks' => 'Snacks',
                        'drinks' => 'Drinks',
                    ]),
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
            'index'  => Pages\ListKitchenItems::route('/'),
            'create' => Pages\CreateKitchenItem::route('/create'),
            'edit'   => Pages\EditKitchenItem::route('/{record}/edit'),
        ];
    }
}
