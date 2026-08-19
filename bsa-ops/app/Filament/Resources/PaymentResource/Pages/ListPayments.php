<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentStatus::Completed)),
            'pending' => Tab::make('Pending verification')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentStatus::PendingVerification)),
            'bounced' => Tab::make('Bounced')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentStatus::Bounced)),
        ];
    }
}
