<?php

namespace App\Filament\Resources;

use App\Enums\ExpenseStatus;
use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\NumberSequenceService;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('expense_category_id')
                    ->label('Category')
                    ->options(ExpenseCategory::active()->orderBy('sort_order')->pluck('name', 'id'))
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('department_id')
                    ->label('Department')
                    ->options(Department::active()->pluck('name', 'id'))
                    ->helperText('Leave empty for shared overhead (rent, admin salaries…)')
                    ->native(false),
                Forms\Components\DatePicker::make('expense_date')->default(today())->required()->native(false),
                Forms\Components\TextInput::make('description')->required()->columnSpan(2),
                Forms\Components\TextInput::make('amount')
                    ->label('Amount (NPR)')
                    ->numeric()
                    ->required()
                    ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 100 : null)
                    ->dehydrateStateUsing(fn ($state) => Money::toPaisa($state)),
                Forms\Components\Select::make('payment_method')
                    ->options(PaymentMethod::class)
                    ->default(PaymentMethod::Cash)
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('vendor_name'),
                Forms\Components\TextInput::make('reference_no')->label('Bill / PAN bill no.'),
                Forms\Components\Textarea::make('notes')->columnSpan(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('expense_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('voucher_number')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('expense_date')->date('j M Y')->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->description(fn (Expense $record) => $record->vendor_name)
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('category.name')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('department.name')
                    ->placeholder('Overhead')
                    ->badge()
                    ->color(fn (?string $state) => $state ? 'info' : 'warning'),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state) => Money::npr($state))
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->badge()->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('recordedBy.name')->label('By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('expense_category_id')
                    ->label('Category')
                    ->options(ExpenseCategory::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('status')->options(ExpenseStatus::class),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (Expense $record) => $record->status === ExpenseStatus::Recorded
                        && auth()->user()->isAtLeast(StaffRole::Manager))
                    ->requiresConfirmation()
                    ->action(function (Expense $record) {
                        $record->update([
                            'status' => ExpenseStatus::Approved,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()->success()->title('Expense approved')->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Expense $record) => $record->status === ExpenseStatus::Recorded)
                    ->slideOver(),
            ]);
    }

    public static function mutateCreate(array $data): array
    {
        $data['voucher_number'] = app(NumberSequenceService::class)->voucherNumber();
        $data['recorded_by'] = auth()->id();

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageExpenses::route('/'),
        ];
    }
}
