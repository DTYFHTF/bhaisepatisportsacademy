<?php

namespace App\Filament\Resources;

use App\Enums\SubscriptionStatus;
use App\Filament\Resources\MemberSubscriptionResource\Pages;
use App\Models\MembershipPlan;
use App\Models\MemberSubscription;
use App\Services\SubscriptionService;
use App\Support\Money;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class MemberSubscriptionResource extends Resource
{
    protected static ?string $model = MemberSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Membership';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'subscription';

    public static function canCreate(): bool
    {
        return false; // sold from the member profile, never created bare
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('starts_on', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['member', 'plan']))
            ->columns([
                Tables\Columns\TextColumn::make('member.member_code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\TextColumn::make('member.first_name')
                    ->label('Member')
                    ->formatStateUsing(fn (MemberSubscription $record) => $record->member->full_name)
                    ->description(fn (MemberSubscription $record) => $record->member->phone)
                    ->searchable(['first_name', 'last_name'])
                    ->url(fn (MemberSubscription $record) => MemberResource::getUrl('view', ['record' => $record->member])),
                Tables\Columns\TextColumn::make('plan.name')
                    ->description(fn (MemberSubscription $record) => $record->plan->departments->pluck('name')->implode(', ')),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('starts_on')->date('j M Y')->sortable(),
                Tables\Columns\TextColumn::make('ends_on')->date('j M Y')->sortable()
                    ->color(fn (MemberSubscription $record) => $record->ends_on === null ? null
                        : ($record->ends_on->isPast() ? 'danger'
                            : ($record->ends_on->lte(today()->addDays(7)) ? 'warning' : null))),
                Tables\Columns\TextColumn::make('sessions_remaining')
                    ->label('Sessions')
                    ->formatStateUsing(fn (MemberSubscription $record) => $record->sessions_total
                        ? "{$record->sessions_remaining}/{$record->sessions_total}"
                        : '—'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Charged')
                    ->formatStateUsing(fn (MemberSubscription $record) => Money::npr($record->netPrice()))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('renewedFrom.id')
                    ->label('Renewal')
                    ->formatStateUsing(fn (?string $state) => $state ? 'Renewal' : 'New')
                    ->badge()
                    ->color(fn (?string $state) => $state ? 'info' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('membership_plan_id')
                    ->label('Plan')
                    ->options(MembershipPlan::orderBy('sort_order')->pluck('name', 'id')),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->native(false),
                        Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn (Builder $q, $d) => $q->whereDate('starts_on', '>=', $d))
                        ->when($data['until'], fn (Builder $q, $d) => $q->whereDate('starts_on', '<=', $d))),
            ])
            ->actions([
                Tables\Actions\Action::make('renew')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->visible(fn (MemberSubscription $record) => in_array($record->status,
                        [SubscriptionStatus::Active, SubscriptionStatus::Expired], true))
                    ->requiresConfirmation()
                    ->action(function (MemberSubscription $record) {
                        $new = app(SubscriptionService::class)->renew($record, creator: auth()->user());
                        Notification::make()->success()
                            ->title("Renewed — invoice {$new->invoice->invoice_number}")
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
                                $record, Carbon::parse($data['from']), Carbon::parse($data['to']),
                                $data['reason'] ?? null, auth()->user(),
                            );
                            Notification::make()->success()->title('Frozen')->send();
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
                        app(SubscriptionService::class)->unfreeze($record);
                        Notification::make()->success()->title('Freeze lifted')->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMemberSubscriptions::route('/'),
        ];
    }
}
