<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityResource\Pages;
use App\Models\Facility;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
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

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Facilities';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Facility Details')->schema([
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

                Textarea::make('description')->rows(3)->maxLength(1000),

                Grid::make(2)->schema([
                    Select::make('category')
                        ->options(['BADMINTON' => 'Badminton', 'GYM' => 'Gym', 'SAUNA' => 'Sauna'])
                        ->required(),
                    TextInput::make('icon')->maxLength(100)->placeholder('e.g. 🏸 or heroicon-o-trophy'),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('hours')
                        ->label('Opening Hours')
                        ->maxLength(255)
                        ->placeholder('e.g. 6:00 AM – 9:00 PM'),
                    TextInput::make('capacity')
                        ->label('Capacity')
                        ->maxLength(255)
                        ->placeholder('e.g. 6 courts / 20+ people'),
                ]),

                TagsInput::make('features')->placeholder('Add feature'),

                Grid::make(2)->schema([
                    Toggle::make('is_active')->label('Active')->default(true),
                    TextInput::make('sort_order')->numeric()->default(0),
                ]),
            ]),

            Section::make('Image')
                ->icon('heroicon-o-photo')
                ->description('Upload a photo for this facility — it goes straight to Cloudinary and appears on the site.')
                ->schema([
                    FileUpload::make('cloudinary_id')
                        ->label('Facility image')
                        ->image()
                        ->disk('cloudinary')
                        ->directory('bsa/facilities')
                        ->visibility('public')
                        ->imagePreviewHeight('240')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(8192)
                        ->columnSpanFull(),
                    TextInput::make('image_url')
                        ->label('Image URL (auto-filled on upload)')
                        ->url()
                        ->readOnly()
                        ->placeholder('Auto-filled after upload')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')->label('')->height(40)->square(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category')->badge(),
                TextColumn::make('icon'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(['BADMINTON' => 'Badminton', 'GYM' => 'Gym', 'SAUNA' => 'Sauna']),
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
            'index'  => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit'   => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
