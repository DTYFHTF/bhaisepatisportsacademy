<?php

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
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

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Bookings';

    protected static ?string $recordTitleAttribute = 'ref';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Customer')
                ->icon('heroicon-o-user')
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
                        TextInput::make('total_duration')
                            ->numeric()
                            ->suffix('min')
                            ->disabled(),
                        TextInput::make('total')
                            ->numeric()
                            ->prefix('NPR')
                            ->disabled()
                            ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 0) : '0'),
                    ]),
                    Textarea::make('notes')->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ref')->label('Ref')->searchable()->sortable(),
                TextColumn::make('customer_name')->searchable(),
                TextColumn::make('customer_phone'),
                TextColumn::make('scheduled_date')->date()->sortable(),
                TextColumn::make('scheduled_time'),
                TextColumn::make('status')->badge()
                    ->color(fn (BookingStatus $state) => match ($state) {
                        BookingStatus::PENDING   => 'warning',
                        BookingStatus::CONFIRMED => 'info',
                        BookingStatus::COMPLETED => 'success',
                        BookingStatus::NO_SHOW   => 'danger',
                        BookingStatus::CANCELLED => 'gray',
                    }),
                TextColumn::make('total')
                    ->formatStateUsing(fn (int $state) => 'NPR ' . number_format($state / 100, 0))
                    ->sortable(),
                TextColumn::make('total_duration')->suffix(' min'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(BookingStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->name])),
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

    public static function getRelations(): array
    {
        return [
            BookingResource\RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit'   => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
