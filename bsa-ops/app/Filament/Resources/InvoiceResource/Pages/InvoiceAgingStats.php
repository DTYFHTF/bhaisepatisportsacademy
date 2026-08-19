<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Models\Invoice;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class InvoiceAgingStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $aging = fn (int $fromDays, ?int $toDays) => (int) Invoice::outstanding()
            ->whereDate('due_date', '<=', today()->subDays($fromDays))
            ->when($toDays !== null, fn (Builder $query) => $query->whereDate('due_date', '>', today()->subDays($toDays)))
            ->sum('balance');

        return [
            Stat::make('Current (not yet due)', Money::npr(
                (int) Invoice::outstanding()->whereDate('due_date', '>', today())->sum('balance')
            )),
            Stat::make('Due 0–30 days', Money::npr($aging(0, 30)))->color('warning'),
            Stat::make('31–60 days', Money::npr($aging(31, 60)))->color('danger'),
            Stat::make('60+ days', Money::npr($aging(61, null)))->color('danger'),
        ];
    }
}
