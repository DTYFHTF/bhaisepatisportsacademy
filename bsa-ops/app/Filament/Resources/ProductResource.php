<?php

namespace App\Filament\Resources;

use App\Enums\ProductCategory;
use App\Enums\StaffRole;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Department;
use App\Models\Product;
use App\Services\InventoryService;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return ['sku', 'name'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('sku')->label('SKU')->required()->maxLength(30)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')->required()->columnSpan(2),
                        Forms\Components\Select::make('category')
                            ->options(ProductCategory::class)
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('department_id')
                            ->label('Department (cost center)')
                            ->options(Department::active()->orderBy('sort_order')->pluck('name', 'id'))
                            ->helperText('Where revenue and consumption of this item land in the P&L.')
                            ->native(false),
                        Forms\Components\TextInput::make('unit')->default('piece')->maxLength(20),
                    ]),
                ]),
            Forms\Components\Section::make('Pricing')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Cost (NPR)')
                            ->numeric()
                            ->default(0)
                            ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 100 : 0)
                            ->dehydrateStateUsing(fn ($state) => Money::toPaisa($state)),
                        Forms\Components\TextInput::make('price')
                            ->label('Walk-in price (NPR)')
                            ->numeric()
                            ->required()
                            ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => Money::toPaisa($state)),
                        Forms\Components\TextInput::make('member_price')
                            ->label('Member price (NPR)')
                            ->helperText('Club price; empty = same as walk-in')
                            ->numeric()
                            ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Money::toPaisa($state) : null),
                        Forms\Components\Toggle::make('is_taxable')->default(true)->inline(false),
                        Forms\Components\Toggle::make('price_includes_tax')->default(true)->inline(false),
                        Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                    ]),
                ]),
            Forms\Components\Section::make('Stock')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Toggle::make('track_stock')
                            ->helperText('Off for made-to-order kitchen dishes')
                            ->default(true)
                            ->inline(false),
                        Forms\Components\TextInput::make('reorder_level')->numeric()->default(0),
                        Forms\Components\Placeholder::make('stock_note')
                            ->label('Stock on hand')
                            ->content(fn (?Product $record) => $record
                                ? "{$record->stock_on_hand} {$record->unit}(s) — change via Purchases, Issue stock, or Adjust"
                                : 'Set by receiving a purchase'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('category')
            ->columns([
                Tables\Columns\TextColumn::make('sku')->label('SKU')->badge()->color('gray')->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Product $record) => "per {$record->unit}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('department.name')->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Cost')
                    ->formatStateUsing(fn (int $state) => Money::npr($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('price')
                    ->label('Walk-in')
                    ->formatStateUsing(fn (int $state) => Money::npr($state)),
                Tables\Columns\TextColumn::make('member_price')
                    ->label('Member')
                    ->formatStateUsing(fn (?int $state) => $state !== null ? Money::npr($state) : '—')
                    ->color('success'),
                Tables\Columns\TextColumn::make('stock_on_hand')
                    ->label('Stock')
                    ->formatStateUsing(fn (Product $record) => $record->track_stock ? $record->stock_on_hand : '—')
                    ->color(fn (Product $record) => $record->isLowStock() ? 'danger' : null)
                    ->weight(fn (Product $record) => $record->isLowStock() ? 'bold' : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('reorder_level')->label('Reorder at')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options(ProductCategory::class),
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::pluck('name', 'id')),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low stock')
                    ->query(fn (Builder $query) => $query->lowStock()),
            ])
            ->actions([
                Tables\Actions\Action::make('issue')
                    ->label('Issue')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('warning')
                    ->visible(fn (Product $record) => $record->track_stock)
                    ->form(fn (Product $record) => [
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->helperText("{$record->stock_on_hand} {$record->unit}(s) on hand"),
                        Forms\Components\Select::make('department_id')
                            ->label('Issued to department')
                            ->options(Department::active()->orderBy('sort_order')->pluck('name', 'id'))
                            ->default($record->department_id)
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('notes')->placeholder('e.g. Court 2 tournament'),
                    ])
                    ->action(function (Product $record, array $data) {
                        try {
                            app(InventoryService::class)->consume(
                                $record,
                                (int) $data['quantity'],
                                Department::find($data['department_id']),
                                auth()->user(),
                                $data['notes'] ?? null,
                            );
                            Notification::make()->success()
                                ->title("Issued {$data['quantity']} {$record->unit}(s) of {$record->name}")
                                ->send();
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Cannot issue')
                                ->body(collect($e->errors())->flatten()->first())->send();
                        }
                    }),
                Tables\Actions\Action::make('adjust')
                    ->label('Adjust')
                    ->icon('heroicon-m-scale')
                    ->color('gray')
                    ->visible(fn (Product $record) => $record->track_stock
                        && auth()->user()->isAtLeast(StaffRole::Manager))
                    ->form(fn (Product $record) => [
                        Forms\Components\TextInput::make('counted')
                            ->label('Counted quantity')
                            ->numeric()
                            ->minValue(0)
                            ->default($record->stock_on_hand)
                            ->required(),
                        Forms\Components\TextInput::make('reason')->placeholder('Stocktake / damage / loss'),
                    ])
                    ->action(function (Product $record, array $data) {
                        app(InventoryService::class)->adjust(
                            $record, (int) $data['counted'], auth()->user(), $data['reason'] ?? null,
                        );
                        Notification::make()->success()->title('Stock adjusted')->send();
                    }),
                Tables\Actions\EditAction::make()->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
        ];
    }
}
