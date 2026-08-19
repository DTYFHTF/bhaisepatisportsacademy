<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseCategoryResource\Pages;
use App\Models\ExpenseCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'expense category';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent category')
                    ->options(fn (?ExpenseCategory $record) => ExpenseCategory::query()
                        ->when($record, fn ($q) => $q->whereKeyNot($record->id))
                        ->pluck('name', 'id'))
                    ->native(false),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('code')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('parent.name')->placeholder('—'),
                Tables\Columns\TextColumn::make('expenses_count')->counts('expenses')->label('Expenses'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageExpenseCategories::route('/'),
        ];
    }
}
