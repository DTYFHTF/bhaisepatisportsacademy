<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachResource\Pages;
use App\Models\Coach;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
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

class CoachResource extends Resource
{
    protected static ?string $model = Coach::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Coaches';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Coach Details')->schema([
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

                Grid::make(2)->schema([
                    TextInput::make('role')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Head Badminton Coach'),
                    TextInput::make('credentials')
                        ->maxLength(255)
                        ->placeholder('e.g. BWF Level 2 Certified'),
                ]),

                Textarea::make('bio')->rows(4)->maxLength(1000)
                    ->placeholder('Short introduction — background, coaching philosophy, achievements.'),

                Grid::make(2)->schema([
                    TextInput::make('experience_years')
                        ->numeric()
                        ->suffix('years')
                        ->minValue(0)
                        ->maxValue(70),
                    TagsInput::make('specialties')
                        ->placeholder('Add a specialty')
                        ->helperText('e.g. Singles, Footwork, Youth coaching'),
                ]),

                Grid::make(2)->schema([
                    Toggle::make('is_active')->label('Active')->default(true),
                    TextInput::make('sort_order')->numeric()->default(0),
                ]),
            ]),

            Section::make('Photo')
                ->icon('heroicon-o-photo')
                ->description('Upload a headshot — it goes straight to Cloudinary and appears on the site.')
                ->schema([
                    FileUpload::make('cloudinary_id')
                        ->label('Coach photo')
                        ->image()
                        ->disk('cloudinary')
                        ->directory('bsa/coaches')
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
                ImageColumn::make('image_url')->label('')->height(44)->circular(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('role')->searchable(),
                TextColumn::make('credentials')->toggleable(),
                TextColumn::make('experience_years')->label('Years')->numeric()->sortable(),
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
            'index'  => Pages\ListCoaches::route('/'),
            'create' => Pages\CreateCoach::route('/create'),
            'edit'   => Pages\EditCoach::route('/{record}/edit'),
        ];
    }
}
