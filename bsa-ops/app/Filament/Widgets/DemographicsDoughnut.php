<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Filament\Widgets\ChartWidget;

class DemographicsDoughnut extends ChartWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Members by age band';

    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $bands = [
            'Under 18' => [0, 17],
            '18–25' => [18, 25],
            '26–35' => [26, 35],
            '36–50' => [36, 50],
            '50+' => [51, 200],
        ];

        $counts = [];

        foreach ($bands as $label => [$min, $max]) {
            $counts[$label] = Member::query()
                ->whereNotNull('date_of_birth')
                ->whereDate('date_of_birth', '<=', today()->subYears($min))
                ->whereDate('date_of_birth', '>', today()->subYears($max + 1))
                ->count();
        }

        $counts['Unknown'] = Member::whereNull('date_of_birth')->count();

        return [
            'datasets' => [
                ['data' => array_values($counts)],
            ],
            'labels' => array_keys($counts),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
