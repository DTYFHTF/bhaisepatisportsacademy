<?php

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Filament\Resources\TrialBookingResource\Pages;
use App\Models\Booking;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrialBookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Trial Sessions';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?string $recordTitleAttribute = 'ref';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Customer Information')
                ->icon('heroicon-o-user')
                ->description('Details of the person booking the trial session.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('ref')
                            ->label('Reference')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Select::make('status')
                            ->options(collect(BookingStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->name]))
                            ->required(),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer_phone')
                            ->required()
                            ->tel()
                            ->maxLength(10),
                        TextInput::make('customer_email')
                            ->email()
                            ->maxLength(255),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('age')
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(120),
                        Select::make('experience_level')
                            ->options([
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced',
                            ])
                            ->required(),
                    ]),
                ]),

            Section::make('Session Details')
                ->icon('heroicon-o-calendar')
                ->description('When and what the trial session covers.')
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('scheduled_date')
                            ->required()
                            ->native(false),
                        TextInput::make('scheduled_time')
                            ->required()
                            ->placeholder('10:00'),
                    ]),
                    Textarea::make('goals')
                        ->label('Goals / Interests')
                        ->placeholder('e.g., improve fitness, learn badminton basics, try a new sport')
                        ->rows(3)
                        ->maxLength(500),
                    Textarea::make('notes')
                        ->rows(2)
                        ->placeholder('Any additional notes or requirements'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('type', 'trial'))
            ->columns([
                TextColumn::make('ref')->label('Ref')->searchable()->sortable(),
                TextColumn::make('customer_name')->label('Name')->searchable(),
                TextColumn::make('customer_phone')->label('Phone'),
                TextColumn::make('age')->label('Age'),
                TextColumn::make('experience_level')
                    ->label('Level')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'beginner' => 'info',
                        'intermediate' => 'warning',
                        'advanced' => 'success',
                    }),
                TextColumn::make('scheduled_date')->label('Date')->date()->sortable(),
                TextColumn::make('scheduled_time')->label('Time'),
                TextColumn::make('status')->badge()
                    ->color(fn (BookingStatus $state) => match ($state) {
                        BookingStatus::PENDING   => 'warning',
                        BookingStatus::CONFIRMED => 'info',
                        BookingStatus::COMPLETED => 'success',
                        BookingStatus::NO_SHOW   => 'danger',
                        BookingStatus::CANCELLED => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(BookingStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->name])),
                Tables\Filters\SelectFilter::make('experience_level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTrialBookings::route('/'),
            'create' => Pages\CreateTrialBooking::route('/create'),
            'edit'   => Pages\EditTrialBooking::route('/{record}/edit'),
        ];
    }
}
