<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Support\Money;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Low stock — reorder soon';

    public static function canView(): bool
    {
        return Product::lowStock()->active()->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->lowStock()->active()->orderBy('stock_on_hand'))
            ->paginated([5, 10])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Product $record) => $record->sku),
                Tables\Columns\TextColumn::make('stock_on_hand')
                    ->label('On hand')
                    ->formatStateUsing(fn (Product $record) => "{$record->stock_on_hand} {$record->unit}(s)")
                    ->color('danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('reorder_level')->label('Reorder at'),
                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Unit cost')
                    ->formatStateUsing(fn (int $state) => Money::npr($state)),
                Tables\Columns\TextColumn::make('department.name')->placeholder('—'),
            ]);
    }
}
