<?php

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Support\Money;
use Filament\Forms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false; // raised by the subscription flow
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['invoice_number'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('issue_date', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('member'))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('member.first_name')
                    ->label('Member')
                    ->formatStateUsing(fn (Invoice $record) => $record->member?->full_name)
                    ->description(fn (Invoice $record) => $record->member?->member_code)
                    ->placeholder('Walk-in')
                    ->searchable(['first_name', 'last_name', 'member_code'])
                    ->url(fn (Invoice $record) => $record->member
                        ? MemberResource::getUrl('view', ['record' => $record->member])
                        : null),
                Tables\Columns\TextColumn::make('source')->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('issue_date')->date('j M Y')->sortable(),
                Tables\Columns\TextColumn::make('due_date')->date('j M Y')->sortable()
                    ->color(fn (Invoice $record) => $record->balance > 0 && $record->due_date->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn (int $state) => Money::npr($state)),
                Tables\Columns\TextColumn::make('paid_total')->label('Paid')
                    ->formatStateUsing(fn (int $state) => Money::npr($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('balance')
                    ->formatStateUsing(fn (int $state) => Money::npr($state))
                    ->color(fn (int $state) => $state > 0 ? 'danger' : 'gray')
                    ->weight(fn (int $state) => $state > 0 ? 'bold' : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('tax_total')->label('VAT')
                    ->formatStateUsing(fn (int $state) => Money::npr($state))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(InvoiceStatus::class),
                Tables\Filters\SelectFilter::make('source')->options(\App\Enums\InvoiceSource::class),
                Tables\Filters\Filter::make('issue_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->native(false),
                        Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn (Builder $q, $d) => $q->whereDate('issue_date', '>=', $d))
                        ->when($data['until'], fn (Builder $q, $d) => $q->whereDate('issue_date', '<=', $d))),
            ])
            ->actions([
                Tables\Actions\Action::make('recordPayment')
                    ->label('Payment')
                    ->icon('heroicon-m-banknotes')
                    ->color('warning')
                    ->visible(fn (Invoice $record) => $record->balance > 0)
                    ->form(fn (Invoice $record) => [
                        Forms\Components\TextInput::make('amount_rupees')
                            ->label('Amount (NPR)')
                            ->numeric()
                            ->required()
                            ->default($record->balance / 100),
                        Forms\Components\Select::make('method')
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::Cash)
                            ->required()
                            ->live()
                            ->native(false),
                        Forms\Components\TextInput::make('gateway_txn_id')
                            ->label('Transaction ID')
                            ->visible(fn (Forms\Get $get) => in_array($get('method'),
                                ['esewa', 'khalti', 'bank_transfer', PaymentMethod::Esewa, PaymentMethod::Khalti, PaymentMethod::BankTransfer], true)),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('cheque_number'),
                            Forms\Components\TextInput::make('cheque_bank'),
                            Forms\Components\DatePicker::make('cheque_date')->native(false),
                        ])->visible(fn (Forms\Get $get) => $get('method') === 'cheque' || $get('method') === PaymentMethod::Cheque),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        try {
                            $payment = app(BillingService::class)->applyPayment(
                                $record,
                                [...$data, 'amount' => Money::toPaisa($data['amount_rupees'])],
                                receiver: auth()->user(),
                            );
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Payment rejected')
                                ->body(collect($e->errors())->flatten()->first())->send();

                            return;
                        }

                        Notification::make()->success()->title("Receipt {$payment->receipt_number}")->send();
                    }),
                Tables\Actions\Action::make('void')
                    ->icon('heroicon-m-no-symbol')
                    ->color('danger')
                    ->visible(fn (Invoice $record) => $record->paid_total === 0
                        && $record->status !== InvoiceStatus::Void
                        && auth()->user()->isAtLeast(StaffRole::Manager))
                    ->form([
                        Forms\Components\Textarea::make('reason')->required(),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        try {
                            app(BillingService::class)->voidInvoice($record, auth()->user(), $data['reason']);
                            Notification::make()->success()->title('Invoice voided')->send();
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Cannot void')
                                ->body(collect($e->errors())->flatten()->first())->send();
                        }
                    }),
                Tables\Actions\ViewAction::make()->label(''),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make()
                ->compact()
                ->schema([
                    Grid::make(6)->schema([
                        TextEntry::make('invoice_number')->weight('bold')->copyable(),
                        TextEntry::make('member.full_name')
                            ->label('Member')
                            ->state(fn (Invoice $record) => $record->member?->full_name ?? 'Walk-in'),
                        TextEntry::make('issue_date')->date('j M Y'),
                        TextEntry::make('due_date')->date('j M Y'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('balance')
                            ->formatStateUsing(fn (int $state) => Money::npr($state, true))
                            ->color(fn (int $state) => $state > 0 ? 'danger' : 'success')
                            ->weight('bold'),
                    ]),
                ]),
            Section::make('Lines')
                ->compact()
                ->schema([
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('description')->hiddenLabel()->columnSpan(3),
                            TextEntry::make('quantity')->hiddenLabel()->prefix('×'),
                            TextEntry::make('unit_price')->hiddenLabel()
                                ->formatStateUsing(fn (int $state) => Money::npr($state, true)),
                            TextEntry::make('discount_amount')->hiddenLabel()
                                ->formatStateUsing(fn (int $state) => $state > 0 ? '−' . Money::npr($state, true) : ''),
                            TextEntry::make('tax_amount')->hiddenLabel()
                                ->formatStateUsing(fn (int $state) => $state > 0 ? 'VAT ' . Money::npr($state, true) : ''),
                            TextEntry::make('line_total')->hiddenLabel()
                                ->formatStateUsing(fn (int $state) => Money::npr($state, true))
                                ->weight('bold'),
                        ])
                        ->columns(8),
                    Grid::make(4)->schema([
                        TextEntry::make('subtotal')->formatStateUsing(fn (int $state) => Money::npr($state, true)),
                        TextEntry::make('discount_total')->formatStateUsing(fn (int $state) => Money::npr($state, true)),
                        TextEntry::make('tax_total')->label('VAT')->formatStateUsing(fn (int $state) => Money::npr($state, true)),
                        TextEntry::make('total')->formatStateUsing(fn (int $state) => Money::npr($state, true))->weight('bold'),
                    ]),
                ]),
            Section::make('Void details')
                ->compact()
                ->visible(fn (Invoice $record) => $record->voided_at !== null)
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('voided_at')->dateTime('j M Y H:i'),
                        TextEntry::make('voidedBy.name')->label('Voided by'),
                        TextEntry::make('void_reason'),
                    ]),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
