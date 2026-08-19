<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Support\Money;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('issue_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->copyable(),
                Tables\Columns\TextColumn::make('issue_date')->date('j M Y'),
                Tables\Columns\TextColumn::make('due_date')->date('j M Y'),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn (int $state) => Money::npr($state)),
                Tables\Columns\TextColumn::make('paid_total')->label('Paid')->formatStateUsing(fn (int $state) => Money::npr($state)),
                Tables\Columns\TextColumn::make('balance')
                    ->formatStateUsing(fn (int $state) => Money::npr($state))
                    ->color(fn (int $state) => $state > 0 ? 'danger' : 'gray')
                    ->weight(fn (int $state) => $state > 0 ? 'bold' : null),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('recordPayment')
                    ->label('Record payment')
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
            ]);
    }
}
