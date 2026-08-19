<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    public function getTabs(): array
    {
        return [
            'outstanding' => Tab::make('Outstanding')
                ->modifyQueryUsing(fn (Builder $query) => $query->outstanding()),
            'overdue' => Tab::make('Overdue')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvoiceStatus::Overdue)),
            'partial' => Tab::make('Partially paid')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvoiceStatus::PartiallyPaid)),
            'paid' => Tab::make('Paid')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvoiceStatus::Paid)),
            'void' => Tab::make('Void')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvoiceStatus::Void)),
            'all' => Tab::make('All'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InvoiceAgingStats::class,
        ];
    }
}
