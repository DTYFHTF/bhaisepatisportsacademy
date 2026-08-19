<?php

namespace App\Filament\Pages;

use App\Filament\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Phone-first ordering: what you tap, then what you need to know,
 * then the charts you only read on a desktop.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Today';

    public function getWidgets(): array
    {
        return [
            Widgets\QuickActions::class,
            Widgets\OpsStatsOverview::class,
            Widgets\ExpiringSubscriptionsTable::class,
            Widgets\LowStockTable::class,
            Widgets\RevenueTrendChart::class,
            Widgets\DepartmentRevenueVsExpenseChart::class,
            Widgets\DemographicsDoughnut::class,
        ];
    }

    /**
     * One column on phones; two from large screens up.
     */
    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }
}
