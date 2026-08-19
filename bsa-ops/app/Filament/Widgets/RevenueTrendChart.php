<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class RevenueTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Revenue — last 12 months';

    protected static ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = ['lg' => 2];

    protected function getData(): array
    {
        // Grouped in PHP so sqlite and mysql behave identically.
        $months = collect(range(11, 0))
            ->map(fn (int $i) => now()->subMonthsNoOverflow($i)->startOfMonth());

        $values = $months->map(fn ($month) => (int) Payment::completed()
            ->whereBetween('received_at', [$month, $month->copy()->endOfMonth()])
            ->sum('amount') / 100);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (NPR)',
                    'data' => $values->all(),
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months->map(fn ($m) => $m->format('M y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
