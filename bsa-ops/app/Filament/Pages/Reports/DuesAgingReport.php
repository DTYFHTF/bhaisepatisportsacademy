<?php

namespace App\Filament\Pages\Reports;

use App\Models\Invoice;
use App\Support\Money;

class DuesAgingReport extends BaseReport
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Dues aging';

    public function report(): array
    {
        $invoices = Invoice::outstanding()
            ->with('member')
            ->get()
            ->groupBy('member_id');

        $rows = $invoices->map(function ($group) {
            $member = $group->first()->member;
            $balance = (int) $group->sum('balance');
            $oldest = $group->min('due_date');
            $daysOverdue = max(0, (int) $oldest->diffInDays(today(), false));

            $bucket = match (true) {
                $daysOverdue === 0 => 'Current',
                $daysOverdue <= 30 => '0–30 days',
                $daysOverdue <= 60 => '31–60 days',
                default => '60+ days',
            };

            return [
                'sort' => $daysOverdue,
                'row' => [
                    $member->member_code,
                    $member->full_name,
                    $member->phone,
                    $group->count(),
                    Money::npr($balance),
                    $oldest->format('j M Y'),
                    $daysOverdue,
                    $bucket,
                ],
            ];
        })
            ->sortByDesc('sort')
            ->pluck('row')
            ->values()
            ->all();

        return [
            'headers' => ['Code', 'Member', 'Phone', 'Open invoices', 'Balance', 'Oldest due', 'Days overdue', 'Bucket'],
            'rows' => $rows,
        ];
    }
}
