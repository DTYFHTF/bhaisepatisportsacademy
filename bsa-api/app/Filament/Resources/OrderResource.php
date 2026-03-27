<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\SmsService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'order_id';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->searchable()
                    ->label('Customer'),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->toggleable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->alignCenter(),
                TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => 'NPR ' . number_format($state / 100))
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state)
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'KHALTI' => 'purple',
                        'ESEWA' => 'success',
                        'COD' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state)
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'PENDING' => 'warning',
                        'CONFIRMED' => 'primary',
                        'PACKED' => 'info',
                        'DISPATCHED' => 'info',
                        'DELIVERED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('courier')
                    ->label('Courier')
                    ->badge()
                    ->color('info')
                    ->toggleable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->label('Placed'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(
                        fn ($s) => [$s->value => $s->value]
                    )->toArray())
                    ->multiple(),
                SelectFilter::make('payment_method')
                    ->options([
                        'KHALTI' => 'Khalti',
                        'ESEWA' => 'eSewa',
                        'COD' => 'COD',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('change_status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->form([
                        Select::make('status')
                            ->options(collect(OrderStatus::cases())->mapWithKeys(
                                fn ($s) => [$s->value => $s->value]
                            )->toArray())
                            ->required()
                            ->native(false),
                        Textarea::make('note')
                            ->placeholder('Optional note for this status change'),
                    ])
                    ->action(function (Order $record, array $data) {
                        $record->update(['status' => $data['status']]);
                        $record->statusHistory()->create([
                            'status' => $data['status'],
                            'note' => $data['note'] ?? null,
                            'changed_by' => auth()->user()->email,
                        ]);
                        app(SmsService::class)->sendStatusUpdate($record);
                    }),
                Action::make('assign_courier')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->form([
                        Select::make('courier')
                            ->options([
                                'PATHAO' => 'Pathao Parcel',
                                'NCM'    => 'Nepal Can Move',
                                'DHULO'  => 'Dhulo',
                                'OTHER'  => 'Other',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('courier_tracking_id')
                            ->label('Tracking Number')
                            ->placeholder('e.g. PP-123456'),
                        TextInput::make('courier_tracking_url')
                            ->label('Tracking URL (optional)')
                            ->url()
                            ->placeholder('https://...'),
                    ])
                    ->fillForm(fn (Order $record) => [
                        'courier' => $record->courier,
                        'courier_tracking_id' => $record->courier_tracking_id,
                        'courier_tracking_url' => $record->courier_tracking_url,
                    ])
                    ->action(function (Order $record, array $data) {
                        $record->update([
                            'courier' => $data['courier'],
                            'courier_tracking_id' => $data['courier_tracking_id'] ?? null,
                            'courier_tracking_url' => $data['courier_tracking_url'] ?? null,
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Grid::make(3)->schema([

                // ── Left sidebar (1/3) ────────────────────────────────────
                Group::make([
                    Section::make('Customer')
                        ->icon('heroicon-o-user')
                        ->schema([
                            TextEntry::make('customer_name')->label('Name'),
                            TextEntry::make('phone')->copyable()->placeholder('-'),
                            TextEntry::make('address'),
                            TextEntry::make('city'),
                            TextEntry::make('delivery_note')->label('Note')->placeholder('-'),
                        ]),

                    Section::make('Payment')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Grid::make(2)->schema([
                                TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state)
                                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                                        'DELIVERED' => 'success',
                                        'CANCELLED' => 'danger',
                                        'PENDING' => 'warning',
                                        default => 'primary',
                                    }),
                                TextEntry::make('payment_method')
                                    ->label('Method')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state)
                                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                                        'KHALTI' => 'purple',
                                        'ESEWA' => 'success',
                                        'COD' => 'gray',
                                        default => 'gray',
                                    }),
                            ]),
                            TextEntry::make('payment_ref')->label('Ref')->placeholder('-'),
                            Grid::make(1)->schema([
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->formatStateUsing(fn ($state) => 'NPR ' . number_format($state / 100)),
                                TextEntry::make('delivery_fee')
                                    ->label('Delivery')
                                    ->formatStateUsing(fn ($state) => 'NPR ' . number_format($state / 100)),
                                TextEntry::make('total')
                                    ->label('Total')
                                    ->formatStateUsing(fn ($state) => 'NPR ' . number_format($state / 100))
                                    ->weight('bold')
                                    ->size(TextEntry\TextEntrySize::Large),
                            ]),
                        ]),

                    Section::make('Shipping')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            TextEntry::make('courier')
                                ->label('Courier')
                                ->badge()
                                ->color('info')
                                ->formatStateUsing(fn ($state) => match ($state) {
                                    'PATHAO' => 'Pathao Parcel',
                                    'NCM'    => 'Nepal Can Move',
                                    'DHULO'  => 'Dhulo',
                                    'OTHER'  => 'Other',
                                    default  => $state,
                                })
                                ->placeholder('Not assigned'),
                            TextEntry::make('courier_tracking_id')
                                ->label('Tracking No.')
                                ->copyable()
                                ->placeholder('-'),
                            TextEntry::make('courier_tracking_url')
                                ->label('Courier site')
                                ->url(fn ($record) => $record->courier_tracking_url)
                                ->openUrlInNewTab()
                                ->placeholder('-'),
                        ]),
                ])->columnSpan(1),

                // ── Main content area (2/3) ───────────────────────────────
                Group::make([
                    Section::make('Order Items')
                        ->icon('heroicon-o-shopping-cart')
                        ->schema([
                            RepeatableEntry::make('items')
                                ->schema([
                                    TextEntry::make('product.name')->label('Product'),
                                    TextEntry::make('variant.size')->label('Size'),
                                    TextEntry::make('quantity'),
                                    TextEntry::make('unit_price')
                                        ->label('Unit Price')
                                        ->formatStateUsing(fn ($state) => 'NPR ' . number_format($state / 100)),
                                ])
                                ->columns(4),
                        ]),

                    Section::make('Delivery Location')
                        ->icon('heroicon-o-map-pin')
                        ->visible(fn ($record) => $record->latitude !== null)
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            Grid::make(2)->schema([
                                TextEntry::make('nearest_landmark')
                                    ->label('Nearest Landmark')
                                    ->placeholder('-'),
                                TextEntry::make('latitude')
                                    ->label('GPS Coordinates')
                                    ->formatStateUsing(fn ($state, $record) => $record->latitude
                                        ? "{$record->latitude}, {$record->longitude}"
                                        : null)
                                    ->placeholder('-'),
                                TextEntry::make('formatted_address')
                                    ->label('Map Address')
                                    ->placeholder('-')
                                    ->columnSpan(2),
                                TextEntry::make('view_on_map')
                                    ->label('Open in Maps')
                                    ->state(fn ($record) => $record->latitude ? '📍 Customer Location' : null)
                                    ->url(fn ($record) => $record->latitude
                                        ? "https://www.google.com/maps?q={$record->latitude},{$record->longitude}"
                                        : null)
                                    ->openUrlInNewTab()
                                    ->placeholder('-'),
                                TextEntry::make('route_from_store')
                                    ->label('Route from Store')
                                    ->state(fn ($record) => $record->latitude ? '🚗 Google Maps Directions' : null)
                                    ->url(fn ($record) => $record->latitude
                                        ? 'https://www.google.com/maps/dir/?api=1&origin=' . urlencode(env('STORE_ADDRESS', 'Kathmandu, Nepal')) . "&destination={$record->latitude},{$record->longitude}&travelmode=driving"
                                        : null)
                                    ->openUrlInNewTab()
                                    ->placeholder('-'),
                                TextEntry::make('delivery_rider_link')
                                    ->label('Rider Link')
                                    ->helperText('Copy and send to rider')
                                    ->state(fn ($record) => $record->latitude
                                        ? "https://www.google.com/maps/dir/?api=1&destination={$record->latitude},{$record->longitude}&travelmode=driving"
                                        : null)
                                    ->copyable()
                                    ->columnSpan(2)
                                    ->placeholder('-'),
                            ]),
                        ]),

                    Section::make('Status History')
                        ->icon('heroicon-o-clock')
                        ->collapsed()
                        ->schema([
                            RepeatableEntry::make('statusHistory')
                                ->schema([
                                    TextEntry::make('status')
                                        ->badge()
                                        ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state)
                                        ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                                            'DELIVERED' => 'success',
                                            'CANCELLED' => 'danger',
                                            'PENDING' => 'warning',
                                            default => 'primary',
                                        }),
                                    TextEntry::make('note')->placeholder('-'),
                                    TextEntry::make('changed_by')->placeholder('System'),
                                    TextEntry::make('changed_at')
                                        ->dateTime('d M Y, h:i A'),
                                ])
                                ->columns(4),
                        ]),
                ])->columnSpan(2),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
