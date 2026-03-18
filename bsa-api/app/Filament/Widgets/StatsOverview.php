<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Order;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Bookings (7 days)', Booking::where('created_at', '>=', now()->subDays(7))->count())
                ->color('primary'),

            Stat::make('Orders (7 days)', Order::where('created_at', '>=', now()->subDays(7))->count())
                ->color('info'),

            Stat::make('Revenue (7 days)', 'NPR ' . number_format(
                Order::where('created_at', '>=', now()->subDays(7))
                    ->whereNotIn('status', ['CANCELLED'])
                    ->sum('total') / 100
            ))
                ->color('success'),

            Stat::make('Low Stock Items', ProductVariant::whereRaw('stock - reserved_stock <= 3')->count())
                ->color('danger'),
        ];
    }
}
