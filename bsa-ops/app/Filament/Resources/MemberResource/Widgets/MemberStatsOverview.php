<?php

namespace App\Filament\Resources\MemberResource\Widgets;

use App\Enums\MemberStatus;
use App\Models\Invoice;
use App\Models\Member;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MemberStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total members', Member::count()),
            Stat::make('Active', Member::where('status', MemberStatus::Active)->count())
                ->color('success'),
            Stat::make('New this month', Member::where('joined_on', '>=', now()->startOfMonth())->count())
                ->color('info'),
            Stat::make('Outstanding dues', Money::npr((int) Invoice::outstanding()->sum('balance')))
                ->color('danger'),
        ];
    }
}
