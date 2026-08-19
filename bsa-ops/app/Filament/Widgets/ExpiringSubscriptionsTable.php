<?php

namespace App\Filament\Widgets;

use App\Models\MemberSubscription;
use App\Services\SubscriptionService;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ExpiringSubscriptionsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Expiring within 14 days';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MemberSubscription::query()
                    ->expiringWithin(14)
                    ->with(['member', 'plan'])
                    ->orderBy('ends_on')
            )
            ->paginated([5, 10])
            ->columns([
                Tables\Columns\TextColumn::make('member.first_name')
                    ->label('Member')
                    ->formatStateUsing(fn (MemberSubscription $record) => $record->member->full_name)
                    ->description(fn (MemberSubscription $record) => $record->member->phone),
                Tables\Columns\TextColumn::make('plan.name'),
                Tables\Columns\TextColumn::make('ends_on')
                    ->date('j M Y')
                    ->color(fn (MemberSubscription $record) => $record->ends_on->lte(today()->addDays(3)) ? 'danger' : 'warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('renew')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (MemberSubscription $record) {
                        $new = app(SubscriptionService::class)->renew($record, creator: auth()->user());
                        Notification::make()->success()
                            ->title("Renewed — invoice {$new->invoice->invoice_number}")
                            ->send();
                    }),
            ]);
    }
}
