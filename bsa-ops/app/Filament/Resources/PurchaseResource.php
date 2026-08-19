<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 4;

    public static function canEdit($record): bool
    {
        return false; // stock ledger — corrections via Adjust on the product
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::active()->pluck('name', 'id'))
                    ->native(false),
                Forms\Components\DatePicker::make('purchase_date')->default(today())->required()->native(false),
                Forms\Components\TextInput::make('reference_no')->label('Supplier bill no.'),
            ]),
            Forms\Components\Repeater::make('lines')
                ->label('Items received')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Product')
                        ->options(Product::active()->where('track_stock', true)->orderBy('name')
                            ->get()->mapWithKeys(fn (Product $p) => [$p->id => "{$p->name} ({$p->unit})"]))
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('quantity')->numeric()->minValue(1)->default(1)->required(),
                    Forms\Components\TextInput::make('unit_cost_rupees')
                        ->label('Unit cost (NPR)')
                        ->numeric()
                        ->required(),
                ])
                ->columns(4)
                ->minItems(1)
                ->defaultItems(1)
                ->addActionLabel('Add line'),
            Forms\Components\Textarea::make('notes')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('purchase_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('voucher_number')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('purchase_date')->date('j M Y')->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->placeholder('—')->searchable(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Lines'),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn (int $state) => Money::npr($state))
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_no')->label('Bill no.')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('receivedBy.name')->label('Received by')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make()
                ->compact()
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('voucher_number')->weight('bold'),
                        TextEntry::make('supplier.name')->placeholder('—'),
                        TextEntry::make('purchase_date')->date('j M Y'),
                        TextEntry::make('total')->formatStateUsing(fn (int $state) => Money::npr($state, true))->weight('bold'),
                    ]),
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('product.name')->hiddenLabel()->columnSpan(2),
                            TextEntry::make('quantity')->hiddenLabel()->prefix('× '),
                            TextEntry::make('unit_cost')->hiddenLabel()
                                ->formatStateUsing(fn (int $state) => Money::npr($state, true)),
                            TextEntry::make('line_total')->hiddenLabel()
                                ->formatStateUsing(fn (int $state) => Money::npr($state, true))
                                ->weight('bold'),
                        ])
                        ->columns(5),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePurchases::route('/'),
        ];
    }
}
