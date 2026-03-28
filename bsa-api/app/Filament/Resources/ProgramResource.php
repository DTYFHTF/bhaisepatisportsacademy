<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Programs';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Program Details')->schema([
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

                Grid::make(3)->schema([
                    Select::make('category')
                        ->options(['badminton' => 'Badminton', 'gym' => 'Gym', 'sauna' => 'Sauna'])
                        ->required(),
                    Select::make('level')
                        ->options(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced', 'all' => 'All Levels'])
                        ->required(),
                    TextInput::make('age_group')->maxLength(50)->placeholder('e.g. 15+'),
                ]),

                Grid::make(3)->schema([
                    TextInput::make('duration')->maxLength(100)->placeholder('e.g. 8 weeks'),
                    TextInput::make('sessions_per_week')->numeric()->suffix('/week'),
                    TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('NPR')
                        ->helperText('In paisa (e.g. 300000 = NPR 3,000)'),
                ]),

                TagsInput::make('features')->placeholder('Add feature'),

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
                TextColumn::make('level')->badge(),
                TextColumn::make('price')
                    ->formatStateUsing(fn (int $state) => 'NPR ' . number_format($state / 100, 0))
                    ->sortable(),
                IconColumn::make('is_popular')->boolean(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(['badminton' => 'Badminton', 'gym' => 'Gym', 'sauna' => 'Sauna']),
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
            'index'  => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit'   => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
