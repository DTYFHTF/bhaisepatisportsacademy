<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleSlotResource\Pages;
use App\Models\ScheduleSlot;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScheduleSlotResource extends Resource
{
    protected static ?string $model = ScheduleSlot::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Schedule';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Schedule Slot')->schema([
                Grid::make(2)->schema([
                    Select::make('day')
                        ->options([
                            'Sunday'    => 'Sunday',
                            'Monday'    => 'Monday',
                            'Tuesday'   => 'Tuesday',
                            'Wednesday' => 'Wednesday',
                            'Thursday'  => 'Thursday',
                            'Friday'    => 'Friday',
                            'Saturday'  => 'Saturday',
                        ])
                        ->required(),
                    TextInput::make('time')->required()->maxLength(50)->placeholder('e.g. 6:00 AM – 8:00 AM'),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('program_name')->required()->maxLength(255),
                    TextInput::make('court')->maxLength(100)->placeholder('e.g. Court 1 & 2'),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('coach')->maxLength(100),
                    Select::make('level')
                        ->options(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced', 'all' => 'All Levels']),
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
                TextColumn::make('day')->sortable(),
                TextColumn::make('time'),
                TextColumn::make('program_name')->searchable(),
                TextColumn::make('coach'),
                TextColumn::make('level')->badge(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('day')
                    ->options([
                        'Sunday' => 'Sunday', 'Monday' => 'Monday', 'Tuesday' => 'Tuesday',
                        'Wednesday' => 'Wednesday', 'Thursday' => 'Thursday', 'Friday' => 'Friday',
                        'Saturday' => 'Saturday',
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
            'index'  => Pages\ListScheduleSlots::route('/'),
            'create' => Pages\CreateScheduleSlot::route('/create'),
            'edit'   => Pages\EditScheduleSlot::route('/{record}/edit'),
        ];
    }
}
