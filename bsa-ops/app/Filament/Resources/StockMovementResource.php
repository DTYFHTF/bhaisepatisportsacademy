<?php

namespace App\Filament\Resources;

use App\Enums\StockMovementType;
use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\Money;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'stock movement';

    public static function canCreate(): bool
    {
        return false; // append-only ledger — written by the inventory service
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('occurred_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product', 'department', 'createdBy']))
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')->dateTime('j M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->description(fn (StockMovement $record) => $record->product->sku)
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? "+{$state}" : (string) $state)
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('department.name')->placeholder('—'),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Unit cost')
                    ->formatStateUsing(fn (?int $state) => Money::npr($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes')->limit(30)->placeholder('—'),
                Tables\Columns\TextColumn::make('createdBy.name')->label('By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(Product::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('type')->options(StockMovementType::class),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->native(false),
                        Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn (Builder $q, $d) => $q->whereDate('occurred_at', '>=', $d))
                        ->when($data['until'], fn (Builder $q, $d) => $q->whereDate('occurred_at', '<=', $d))),
            ])
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }
}
