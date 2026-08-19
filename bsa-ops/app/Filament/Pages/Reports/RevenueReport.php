<?php

namespace App\Filament\Pages\Reports;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Support\Money;

class RevenueReport extends BaseReport
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Revenue report';

    public function report(): array
    {
        [$from, $until] = $this->range();

        $payments = Payment::completed()
            ->whereBetween('received_at', [$from, $until])
            ->get(['amount', 'method', 'received_at']);

        $methods = PaymentMethod::cases();

        $rows = $payments
            ->groupBy(fn (Payment $p) => $p->received_at->format('Y-m'))
            ->sortKeys()
            ->map(function ($group, $month) use ($methods) {
                $row = [\Illuminate\Support\Carbon::parse("{$month}-01")->format('M Y')];

                foreach ($methods as $method) {
                    $row[] = Money::npr((int) $group->where('method', $method)->sum('amount'));
                }

                $row[] = Money::npr((int) $group->sum('amount'));

                return $row;
            })
            ->values()
            ->all();

        // Grand total row
        if ($payments->isNotEmpty()) {
            $totalRow = ['Total'];
            foreach ($methods as $method) {
                $totalRow[] = Money::npr((int) $payments->where('method', $method)->sum('amount'));
            }
            $totalRow[] = Money::npr((int) $payments->sum('amount'));
            $rows[] = $totalRow;
        }

        return [
            'headers' => ['Month', ...array_map(fn (PaymentMethod $m) => $m->getLabel(), $methods), 'Total'],
            'rows' => $rows,
        ];
    }
}
