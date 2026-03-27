<?php

namespace App\Filament\Resources;

use App\Enums\Category;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(3)->schema([
                // Left column (2/3)
                Grid::make(1)->schema([
                    Section::make('Product Details')
                        ->icon('heroicon-o-cube')
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
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),
                            Grid::make(2)->schema([
                                TextInput::make('tagline')
                                    ->maxLength(100)
                                    ->placeholder('e.g. Gentle post-wax care'),
                                Select::make('category')
                                    ->options(collect(Category::cases())->mapWithKeys(fn ($c) => [$c->value => $c->name]))
                                    ->required()
                                    ->native(false),
                            ]),
                            Textarea::make('description')
                                ->rows(3)
                                ->maxLength(1000),
                        ]),

                    Section::make('Product Information')
                        ->icon('heroicon-o-beaker')
                        ->schema([
                            Textarea::make('ingredients')
                                ->rows(3)
                                ->placeholder('List key ingredients'),
                            Textarea::make('how_to_use')
                                ->label('How to Use')
                                ->rows(3),
                            Textarea::make('suitable_for')
                                ->label('Suitable For')
                                ->rows(2)
                                ->placeholder('e.g. All skin types, sensitive skin'),
                        ]),

                    Section::make('Images')
                        ->icon('heroicon-o-photo')
                        ->description('Upload images - they go straight to Cloudinary.')
                        ->schema([
                            Repeater::make('images')
                                ->relationship()
                                ->schema([
                                    FileUpload::make('cloudinary_id')
                                        ->label('Image')
                                        ->image()
                                        ->disk('cloudinary')
                                        ->directory('bsa/products')
                                        ->visibility('public')
                                        ->imagePreviewHeight('240')
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(8192)
                                        ->columnSpanFull(),
                                    TextInput::make('url')
                                        ->label('Image URL (auto-filled on upload)')
                                        ->url()
                                        ->readOnly()
                                        ->placeholder('Auto-filled after upload')
                                        ->columnSpanFull(),
                                    TextInput::make('alt_text')
                                        ->label('Alt text')
                                        ->placeholder('e.g. Aloe Vera Gel - Product shot')
                                        ->maxLength(200)
                                        ->columnSpanFull(),
                                ])
                                ->orderColumn('order')
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string =>
                                    is_string($state['alt_text'] ?? null) && $state['alt_text'] !== ''
                                        ? $state['alt_text']
                                        : (is_string($state['url'] ?? null) && $state['url'] !== '' ? $state['url'] : 'Image')
                                )
                                ->maxItems(4)
                                ->defaultItems(0)
                                ->addActionLabel('Add image'),
                        ]),

                    Section::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->collapsed()
                        ->schema([
                            TextInput::make('seo_title')
                                ->label('Meta title')
                                ->maxLength(60),
                            Textarea::make('seo_description')
                                ->label('Meta description')
                                ->rows(2)
                                ->maxLength(160),
                        ]),
                ])->columnSpan(2),

                // Right column (1/3) - sidebar
                Grid::make(1)->schema([
                    Section::make('Status')
                        ->schema([
                            Toggle::make('is_active')
                                ->label('Visible on shop')
                                ->default(true)
                                ->onColor('success'),
                        ]),

                    Section::make('Pricing')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            TextInput::make('price')
                                ->numeric()
                                ->prefix('NPR')
                                ->helperText('In paisa (150000 = NPR 1,500)')
                                ->required(),
                            TextInput::make('compare_at_price')
                                ->numeric()
                                ->prefix('NPR')
                                ->helperText('Original price (strike-through)'),
                        ]),

                    Section::make('Tags')
                        ->schema([
                            TagsInput::make('tags')
                                ->suggestions([
                                    'new-arrival', 'bestseller', 'aftercare',
                                    'skincare', 'wax-kit', 'sensitive-skin',
                                ]),
                        ]),
                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images.url')
                    ->label('Image')
                    ->circular()
                    ->stacked()
                    ->limit(1)
                    ->getStateUsing(fn (Product $record): ?string =>
                        $record->images->first()?->url
                    ),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('tagline')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('category')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => 'NPR ' . number_format($state / 100))
                    ->sortable(),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants')
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(collect(Category::cases())->mapWithKeys(fn ($c) => [$c->value => $c->name])),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
