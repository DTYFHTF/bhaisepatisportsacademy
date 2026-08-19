<?php

namespace App\Filament\Resources\MemberSubscriptionResource\Pages;

use App\Enums\SubscriptionStatus;
use App\Filament\Resources\MemberSubscriptionResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMemberSubscriptions extends ListRecords
{
    protected static string $resource = MemberSubscriptionResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::Active)),
            'expiring' => Tab::make('Expiring 14d')
                ->modifyQueryUsing(fn (Builder $query) => $query->expiringWithin(14)),
            'frozen' => Tab::make('Frozen')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::Frozen)),
            'expired' => Tab::make('Expired')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::Expired)),
            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::Cancelled)),
        ];
    }
}
