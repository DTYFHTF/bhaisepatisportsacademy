<?php

namespace App\Filament\Pages\Reports;

use App\Models\Department;
use App\Models\Expense;
use App\Models\InvoiceItem;
use App\Models\MembershipPlan;
use App\Support\Money;

class DepartmentPnlReport extends BaseReport
{
    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Department P&L';

    public function report(): array
    {
        [$from, $until] = $this->range();

        $departments = Department::orderBy('sort_order')->get();

        // Revenue: invoice items -> plan -> departments, split evenly across
        // the departments a plan covers.
        $items = InvoiceItem::query()
            ->where('itemable_type', MembershipPlan::class)
            ->whereHas('invoice', fn ($q) => $q
                ->whereBetween('issue_date', [$from, $until])
                ->whereNull('voided_at'))
            ->with('itemable.departments')
            ->get();

        $revenue = $departments->mapWithKeys(fn (Department $d) => [$d->id => 0])->all();

        foreach ($items as $item) {
            $plan = $item->itemable;

            if (! $plan || $plan->departments->isEmpty()) {
                continue;
            }

            $share = intdiv($item->line_total, $plan->departments->count());

            foreach ($plan->departments as $dept) {
                if (array_key_exists($dept->id, $revenue)) {
                    $revenue[$dept->id] += $share;
                }
            }
        }

        // POS revenue: product invoice items land fully on the product's department.
        $productItems = InvoiceItem::query()
            ->where('itemable_type', \App\Models\Product::class)
            ->whereHas('invoice', fn ($q) => $q
                ->whereBetween('issue_date', [$from, $until])
                ->whereNull('voided_at'))
            ->with('itemable')
            ->get();

        // Cost of goods sold, accumulated alongside the revenue it earned.
        $cogs = $departments->mapWithKeys(fn (Department $d) => [$d->id => 0])->all();

        foreach ($productItems as $item) {
            $deptId = $item->itemable?->department_id;

            if ($deptId && array_key_exists($deptId, $revenue)) {
                $revenue[$deptId] += $item->line_total;
                $cogs[$deptId] += $item->itemable->cost_price * $item->quantity;
            }
        }

        $totalRevenue = array_sum($revenue);

        // Direct costs = recorded expenses + cost of goods sold + internal
        // stock consumption (shuttlecocks issued to the courts, chlorine to
        // the pool), both valued at cost.
        $consumption = \App\Models\StockMovement::query()
            ->where('type', \App\Enums\StockMovementType::Consumption)
            ->whereNotNull('department_id')
            ->whereBetween('occurred_at', [$from, $until])
            ->get()
            ->groupBy('department_id')
            ->map(fn ($group) => (int) $group->sum(fn ($m) => abs($m->quantity) * ($m->unit_cost ?? 0)));

        $direct = $departments->mapWithKeys(fn (Department $d) => [
            $d->id => (int) Expense::where('department_id', $d->id)
                ->whereBetween('expense_date', [$from, $until])
                ->sum('amount')
                + ($consumption[$d->id] ?? 0)
                + ($cogs[$d->id] ?? 0),
        ])->all();

        // Shared overhead allocated pro-rata by revenue share.
        $overheadTotal = (int) Expense::overhead()
            ->whereBetween('expense_date', [$from, $until])
            ->sum('amount');

        $rows = $departments->map(function (Department $d) use ($revenue, $direct, $overheadTotal, $totalRevenue) {
            $rev = $revenue[$d->id];
            $dir = $direct[$d->id];
            $overhead = $totalRevenue > 0 ? intdiv($overheadTotal * $rev, $totalRevenue) : 0;
            $net = $rev - $dir - $overhead;
            $margin = $rev > 0 ? round($net / $rev * 100) . '%' : '—';

            return [
                $d->name,
                Money::npr($rev),
                Money::npr($dir),
                Money::npr($overhead),
                Money::npr($net),
                $margin,
            ];
        })->all();

        $rows[] = [
            'Total',
            Money::npr($totalRevenue),
            Money::npr(array_sum($direct)),
            Money::npr($overheadTotal),
            Money::npr($totalRevenue - array_sum($direct) - $overheadTotal),
            $totalRevenue > 0 ? round(($totalRevenue - array_sum($direct) - $overheadTotal) / $totalRevenue * 100) . '%' : '—',
        ];

        return [
            'headers' => ['Department', 'Revenue', 'Direct expenses', 'Overhead (allocated)', 'Net', 'Margin'],
            'rows' => $rows,
        ];
    }
}
