<?php

namespace App\Filament\Resources\CheckInResource\Pages;

use App\Models\CheckIn;
use App\Models\Department;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayCheckInStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $byDept = CheckIn::today()->allowed()
            ->selectRaw('department_id, count(*) as c')
            ->groupBy('department_id')
            ->pluck('c', 'department_id');

        $stats = [
            Stat::make('Today (all)', CheckIn::today()->allowed()->count()),
            Stat::make('Denied today', CheckIn::today()->where('was_allowed', false)->count())
                ->color('danger'),
        ];

        foreach (Department::active()->accessControlled()->orderBy('sort_order')->limit(4)->get() as $dept) {
            $stats[] = Stat::make($dept->name, $byDept[$dept->id] ?? 0);
        }

        return $stats;
    }
}
