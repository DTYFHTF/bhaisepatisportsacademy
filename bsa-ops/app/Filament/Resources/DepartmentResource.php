<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')->required()->columnSpan(2),
                Forms\Components\TextInput::make('cost_center_code')->maxLength(20),
                Forms\Components\TextInput::make('monthly_budget')
                    ->label('Monthly budget (NPR)')
                    ->numeric()
                    ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 100 : null)
                    ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? Money::toPaisa($state) : null),
                Forms\Components\TextInput::make('capacity')->numeric(),
                Forms\Components\TimePicker::make('opens_at')->seconds(false),
                Forms\Components\TimePicker::make('closes_at')->seconds(false),
                Forms\Components\Select::make('color')
                    ->options([
                        'success' => 'Green', 'info' => 'Blue', 'warning' => 'Amber',
                        'danger' => 'Red', 'gray' => 'Gray',
                    ])
                    ->native(false),
                Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Textarea::make('description')->columnSpan(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('code')->badge()
                    ->color(fn (Department $record) => $record->color ?? 'gray'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('cost_center_code')->placeholder('—'),
                Tables\Columns\TextColumn::make('hours')
                    ->state(fn (Department $record) => $record->opens_at && $record->closes_at
                        ? substr($record->opens_at, 0, 5) . ' – ' . substr($record->closes_at, 0, 5)
                        : '—'),
                Tables\Columns\TextColumn::make('capacity')->placeholder('—'),
                Tables\Columns\TextColumn::make('monthly_budget')
                    ->formatStateUsing(fn (?int $state) => Money::npr($state)),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDepartments::route('/'),
        ];
    }
}
