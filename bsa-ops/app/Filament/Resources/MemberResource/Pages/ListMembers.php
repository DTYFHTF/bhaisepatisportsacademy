<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Enums\MemberStatus;
use App\Enums\SubscriptionStatus;
use App\Filament\Resources\MemberResource;
use App\Filament\Resources\MemberResource\Widgets\MemberStatsOverview;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MemberStatsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', MemberStatus::Active)),
            'expiring' => Tab::make('Expiring 7d')
                ->modifyQueryUsing(fn (Builder $query) => $query->expiringWithin(7)),
            'dues' => Tab::make('With dues')
                ->modifyQueryUsing(fn (Builder $query) => $query->withDues()),
            'frozen' => Tab::make('Frozen')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('subscriptions',
                    fn (Builder $s) => $s->where('status', SubscriptionStatus::Frozen))),
            'expired' => Tab::make('Expired')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', MemberStatus::Expired)),
            'blacklisted' => Tab::make('Blacklisted')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', MemberStatus::Blacklisted)),
        ];
    }
}
