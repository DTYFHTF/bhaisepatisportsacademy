<?php

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Filament\Resources\TrialBookingResource\Pages;
use App\Models\TrialBooking;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrialBookingResource extends Resource
{
    protected static ?string $model = TrialBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Trial Bookings';

    protected static ?string $recordTitleAttribute = 'ref';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Customer')
                ->icon('heroicon-o-user')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('ref')
                            ->label('Trial ID')
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
                        Select::make('trial_type')
                            ->options([
                                'badminton' => 'Badminton',
                                'gym'       => 'Gym',
                                'sauna'     => 'Sauna',
                            ])
                            ->required(),
                    ]),
                ]),

            Section::make('Schedule')
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('scheduled_date')
                            ->required()
                            ->native(false),
                        TextInput::make('scheduled_time')
                            ->required()
                            ->placeholder('10:00'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('duration')
                            ->numeric()
                            ->suffix('min')
                            ->disabled(),
                    ]),
                    Textarea::make('notes')->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ref')->label('Trial ID')->searchable()->sortable(),
                TextColumn::make('trial_type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'badminton' => 'info',
                        'gym'       => 'success',
                        'sauna'     => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('customer_name')->searchable(),
                TextColumn::make('customer_phone'),
                TextColumn::make('scheduled_date')->date()->sortable(),
                TextColumn::make('scheduled_time'),
                TextColumn::make('duration')->suffix(' min'),
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
                Tables\Filters\SelectFilter::make('trial_type')
                    ->options([
                        'badminton' => 'Badminton',
                        'gym'       => 'Gym',
                        'sauna'     => 'Sauna',
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