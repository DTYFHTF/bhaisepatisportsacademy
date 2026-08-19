<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Enums\SubscriptionStatus;
use App\Models\MemberSubscription;
use App\Services\SubscriptionService;
use App\Support\Money;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')
                    ->description(fn (MemberSubscription $record) => $record->plan->code),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('starts_on')->date('j M Y'),
                Tables\Columns\TextColumn::make('ends_on')->date('j M Y')
                    ->color(fn (MemberSubscription $record) => $record->ends_on?->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('sessions_remaining')
                    ->label('Sessions')
                    ->formatStateUsing(fn (MemberSubscription $record) => $record->sessions_total
                        ? "{$record->sessions_remaining} / {$record->sessions_total}"
                        : '—'),
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn (MemberSubscription $record) => Money::npr($record->netPrice())),
            ])
            ->actions([
                Tables\Actions\Action::make('renew')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->visible(fn (MemberSubscription $record) => in_array($record->status,
                        [SubscriptionStatus::Active, SubscriptionStatus::Expired], true))
                    ->requiresConfirmation()
                    ->modalDescription(fn (MemberSubscription $record) => "Renew {$record->plan->name} for " . Money::npr($record->plan->price) . '? A new invoice will be raised.')
                    ->action(function (MemberSubscription $record) {
                        $new = app(SubscriptionService::class)->renew($record, creator: auth()->user());
                        Notification::make()->success()
                            ->title('Renewed')
                            ->body("New term {$new->starts_on->format('j M')} – {$new->ends_on?->format('j M Y')}, invoice {$new->invoice->invoice_number}.")
                            ->send();
                    }),
                Tables\Actions\Action::make('freeze')
                    ->icon('heroicon-m-pause')
                    ->color('info')
                    ->visible(fn (MemberSubscription $record) => $record->status === SubscriptionStatus::Active
                        && $record->plan->freeze_allowance_days > 0)
                    ->form([
                        Forms\Components\DatePicker::make('from')->default(today())->required()->native(false),
                        Forms\Components\DatePicker::make('to')->required()->native(false),
                        Forms\Components\TextInput::make('reason'),
                    ])
                    ->action(function (MemberSubscription $record, array $data) {
                        try {
                            app(SubscriptionService::class)->freeze(
                                $record,
                                Carbon::parse($data['from']),
                                Carbon::parse($data['to']),
                                $data['reason'] ?? null,
                                auth()->user(),
                            );
                            Notification::make()->success()->title('Subscription frozen')->send();
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Cannot freeze')
                                ->body(collect($e->errors())->flatten()->first())->send();
                        }
                    }),
                Tables\Actions\Action::make('unfreeze')
                    ->icon('heroicon-m-play')
                    ->color('success')
                    ->visible(fn (MemberSubscription $record) => $record->status === SubscriptionStatus::Frozen)
                    ->requiresConfirmation()
                    ->action(function (MemberSubscription $record) {
                        $sub = app(SubscriptionService::class)->unfreeze($record);
                        Notification::make()->success()
                            ->title('Freeze lifted')
                            ->body("New end date: {$sub->ends_on?->format('j M Y')}.")
                            ->send();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (MemberSubscription $record) => in_array($record->status,
                        [SubscriptionStatus::Active, SubscriptionStatus::Frozen, SubscriptionStatus::Pending], true))
                    ->form([
                        Forms\Components\Textarea::make('reason')->required(),
                    ])
                    ->action(function (MemberSubscription $record, array $data) {
                        app(SubscriptionService::class)->cancel($record, $data['reason'], auth()->user());
                        Notification::make()->success()->title('Subscription cancelled')->send();
                    }),
            ]);
    }
}
