<?php

namespace App\Filament\Widgets;

use App\Enums\MemberStatus;
use App\Filament\Resources\CheckInResource;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\MemberResource;
use App\Filament\Resources\PaymentResource;
use App\Models\CheckIn;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Payment;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Every stat is a link — tapping a number takes you to the records
 * behind it, so nobody has to hunt through the sidebar on a phone.
 */
class OpsStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 2; // 2-up on phones, Filament widens it on desktop
    }

    protected function getStats(): array
    {
        $mtd = (int) Payment::completed()
            ->whereBetween('received_at', [now()->startOfMonth(), now()])
            ->sum('amount');

        $lastMonthSamePoint = (int) Payment::completed()
            ->whereBetween('received_at', [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow(),
            ])
            ->sum('amount');

        $delta = $lastMonthSamePoint > 0
            ? (int) round((($mtd - $lastMonthSamePoint) / $lastMonthSamePoint) * 100)
            : null;

        $spark = collect(range(11, 0))
            ->map(fn (int $w) => (int) Payment::completed()
                ->whereBetween('received_at', [
                    now()->subWeeks($w)->startOfWeek(),
                    now()->subWeeks($w)->endOfWeek(),
                ])
                ->sum('amount') / 100)
            ->all();

        $duesTotal = (int) Invoice::outstanding()->sum('balance');

        return [
            Stat::make('Revenue this month', Money::npr($mtd))
                ->description($delta === null ? 'No prior-month baseline' : "{$delta}% vs same point last month")
                ->descriptionIcon($delta !== null && $delta >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($delta !== null && $delta < 0 ? 'danger' : 'success')
                ->chart($spark)
                ->url(PaymentResource::getUrl('index')),

            Stat::make('Active members', Member::where('status', MemberStatus::Active)->count())
                ->description(Member::where('joined_on', '>=', now()->startOfMonth())->count() . ' joined this month')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->url(MemberResource::getUrl('index', ['activeTab' => 'active'])),

            Stat::make('Outstanding dues', Money::npr($duesTotal))
                ->description(Invoice::outstanding()->count() . ' open invoices — tap to collect')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($duesTotal > 0 ? 'danger' : 'success')
                ->url(InvoiceResource::getUrl('index', ['activeTab' => 'outstanding'])),

            Stat::make('Check-ins today', CheckIn::today()->allowed()->count())
                ->description(CheckIn::today()->where('was_allowed', false)->count() . ' denied')
                ->descriptionIcon('heroicon-m-arrow-right-end-on-rectangle')
                ->color('success')
                ->url(CheckInResource::getUrl('index')),
        ];
    }
}
