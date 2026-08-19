<?php

namespace App\Filament\Resources;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\StaffRole;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Services\BillingService;
use App\Support\Money;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false; // recorded against invoices
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['receipt_number', 'gateway_txn_id'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('received_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['member', 'invoice']))
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('member.first_name')
                    ->label('Member')
                    ->formatStateUsing(fn (Payment $record) => $record->member?->full_name)
                    ->description(fn (Payment $record) => $record->invoice->invoice_number)
                    ->placeholder('Walk-in')
                    ->searchable(['first_name', 'last_name'])
                    ->url(fn (Payment $record) => $record->member
                        ? MemberResource::getUrl('view', ['record' => $record->member])
                        : null),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state) => Money::npr($state))
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('received_at')->dateTime('j M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('gateway_txn_id')->label('Txn ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cheque_number')
                    ->description(fn (Payment $record) => $record->cheque_bank)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('receivedBy.name')->label('Received by')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('method')->options(PaymentMethod::class),
                Tables\Filters\SelectFilter::make('status')->options(PaymentStatus::class),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (Payment $record) => $record->status === PaymentStatus::PendingVerification
                        && auth()->user()->isAtLeast(StaffRole::Accountant))
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        app(BillingService::class)->verifyPayment($record, auth()->user());
                        Notification::make()->success()->title('Payment verified')->send();
                    }),
                Tables\Actions\Action::make('bounce')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (Payment $record) => in_array($record->status,
                        [PaymentStatus::PendingVerification, PaymentStatus::Completed], true)
                        && auth()->user()->isAtLeast(StaffRole::Accountant))
                    ->requiresConfirmation()
                    ->modalDescription('Marks the payment as bounced and reopens the invoice balance.')
                    ->action(function (Payment $record) {
                        app(BillingService::class)->bouncePayment($record, auth()->user());
                        Notification::make()->success()->title('Payment bounced')->send();
                    }),
                Tables\Actions\Action::make('refund')
                    ->icon('heroicon-m-receipt-refund')
                    ->color('warning')
                    ->visible(fn (Payment $record) => $record->status === PaymentStatus::Completed
                        && auth()->user()->isAtLeast(StaffRole::Accountant))
                    ->form(fn (Payment $record) => [
                        Forms\Components\TextInput::make('amount_rupees')
                            ->label('Amount (NPR)')
                            ->numeric()
                            ->required()
                            ->default(($record->amount - $record->refunds()->sum('amount')) / 100),
                        Forms\Components\Textarea::make('reason')->required(),
                    ])
                    ->action(function (Payment $record, array $data) {
                        try {
                            app(BillingService::class)->refundPayment(
                                $record, Money::toPaisa($data['amount_rupees']), $data['reason'], auth()->user(),
                            );
                            Notification::make()->success()->title('Refund recorded')->send();
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Cannot refund')
                                ->body(collect($e->errors())->flatten()->first())->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
