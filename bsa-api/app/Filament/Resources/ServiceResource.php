<?php

namespace App\Filament\Resources;

use App\Enums\ServiceCategory;
use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Service Details')
                ->icon('heroicon-o-sparkles')
                ->schema([
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

                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(500),

                    Grid::make(3)->schema([
                        Select::make('category')
                            ->options(collect(ServiceCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->name]))
                            ->required(),
                        TextInput::make('duration')
                            ->required()
                            ->numeric()
                            ->suffix('min'),
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('NPR')
                            ->helperText('In paisa (e.g. 150000 = NPR 1,500)'),
                    ]),

                    TagsInput::make('wax_types')
                        ->placeholder('Add wax type')
                        ->suggestions(['Rica', 'Honey', 'Chocolate', 'Sugar']),

                    Grid::make(3)->schema([
                        Toggle::make('is_popular')->label('Popular'),
                        Toggle::make('is_active')->label('Active')->default(true),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ]),
                ]),

            Section::make('Images')
                ->icon('heroicon-o-photo')
                ->description('Add image URLs for this service (3–5 recommended). Paste any direct image URL — Cloudinary upload is not required.')
                ->collapsible()
                ->schema([
                    Repeater::make('images')
                        ->label(false)
                        ->schema([
                            TextInput::make('url')
                                ->label('Image URL')
                                ->required()
                                ->url()
                                ->placeholder('https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=800&h=1000&q=80')
                                ->columnSpanFull(),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Add image')
                        ->reorderable()
                        ->cloneable()
                        ->maxItems(10)
                        ->dehydrateStateUsing(fn (array $state): array => array_values(array_column($state, 'url')))
                        ->afterStateHydrated(function ($component, $state) {
                            if (is_array($state) && isset($state[0]) && is_string($state[0])) {
                                $component->state(array_map(fn (string $url) => ['url' => $url], $state));
                            }
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category')->badge(),
                TextColumn::make('duration')->suffix(' min')->sortable(),
                TextColumn::make('price')
                    ->formatStateUsing(fn (int $state) => 'NPR ' . number_format($state / 100, 0))
                    ->sortable(),
                IconColumn::make('is_popular')->boolean(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(collect(ServiceCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->name])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
