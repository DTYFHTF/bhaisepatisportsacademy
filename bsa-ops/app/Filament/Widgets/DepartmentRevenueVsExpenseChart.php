<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Expense;
use App\Models\InvoiceItem;
use App\Models\MembershipPlan;
use Filament\Widgets\ChartWidget;

class DepartmentRevenueVsExpenseChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Department revenue vs expenses — this month';

    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $departments = Department::active()->orderBy('sort_order')->get();

        // Revenue attributed via invoice items -> plan -> departments (split
        // evenly when a plan covers several departments).
        $items = InvoiceItem::query()
            ->where('itemable_type', MembershipPlan::class)
            ->whereHas('invoice', fn ($q) => $q
                ->whereBetween('issue_date', [$start, $end])
                ->whereNull('voided_at'))
            ->with('itemable.departments')
            ->get();

        $revenue = $departments->mapWithKeys(fn (Department $d) => [$d->id => 0]);

        foreach ($items as $item) {
            $plan = $item->itemable;

            if (! $plan || $plan->departments->isEmpty()) {
                continue;
            }

            $share = intdiv($item->line_total, $plan->departments->count());

            foreach ($plan->departments as $dept) {
                if ($revenue->has($dept->id)) {
                    $revenue[$dept->id] = $revenue[$dept->id] + $share;
                }
            }
        }

        // POS revenue lands fully on the product's department (kitchen, shop…).
        $productItems = InvoiceItem::query()
            ->where('itemable_type', \App\Models\Product::class)
            ->whereHas('invoice', fn ($q) => $q
                ->whereBetween('issue_date', [$start, $end])
                ->whereNull('voided_at'))
            ->with('itemable')
            ->get();

        foreach ($productItems as $item) {
            $deptId = $item->itemable?->department_id;

            if ($deptId && $revenue->has($deptId)) {
                $revenue[$deptId] = $revenue[$deptId] + $item->line_total;
            }
        }

        $expenses = $departments->mapWithKeys(fn (Department $d) => [
            $d->id => (int) Expense::where('department_id', $d->id)
                ->whereBetween('expense_date', [$start, $end])
                ->sum('amount'),
        ]);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $departments->map(fn (Department $d) => $revenue[$d->id] / 100)->all(),
                ],
                [
                    'label' => 'Expenses (direct)',
                    'data' => $departments->map(fn (Department $d) => $expenses[$d->id] / 100)->all(),
                ],
            ],
            'labels' => $departments->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
