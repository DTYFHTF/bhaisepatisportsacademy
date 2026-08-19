<?php

namespace App\Filament\Pages\Reports;

use App\Models\Member;

class MemberDemographicsReport extends BaseReport
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Member demographics';

    public function report(): array
    {
        [$from, $until] = $this->range();

        $members = Member::whereBetween('joined_on', [$from, $until])->get();
        $total = max(1, $members->count());

        $rows = [];

        $dimension = function (string $label, $groups) use (&$rows, $total) {
            foreach ($groups as $value => $count) {
                $rows[] = [$label, (string) $value, $count, round($count / $total * 100) . '%'];
            }
        };

        $dimension('Gender', $members->groupBy(fn (Member $m) => $m->gender?->getLabel() ?? 'Unknown')->map->count()->sortDesc());

        $dimension('Age band', $members->groupBy(function (Member $m) {
            return match (true) {
                $m->age === null => 'Unknown',
                $m->age < 18 => 'Under 18',
                $m->age <= 25 => '18–25',
                $m->age <= 35 => '26–35',
                $m->age <= 50 => '36–50',
                default => '50+',
            };
        })->map->count()->sortDesc());

        $dimension('Municipality', $members->groupBy(fn (Member $m) => $m->municipality ?? 'Unknown')->map->count()->sortDesc()->take(10));

        $dimension('Referral source', $members->groupBy(fn (Member $m) => $m->referral_source?->getLabel() ?? 'Unknown')->map->count()->sortDesc());

        return [
            'headers' => ['Dimension', 'Value', 'Members joined', 'Share'],
            'rows' => $rows,
        ];
    }
}
