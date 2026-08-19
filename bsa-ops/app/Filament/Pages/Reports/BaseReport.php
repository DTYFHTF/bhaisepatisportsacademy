<?php

namespace App\Filament\Pages\Reports;

use App\Enums\StaffRole;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Report page skeleton: date-range filter, an HTML table, CSV export.
 * Date grouping happens in PHP so sqlite and mysql agree.
 */
abstract class BaseReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.pages.reports.report';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAtLeast(StaffRole::Manager) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'from' => now()->subMonths(5)->startOfMonth()->toDateString(),
            'until' => now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Grid::make(4)->schema([
                Forms\Components\DatePicker::make('from')->native(false)->live(),
                Forms\Components\DatePicker::make('until')->native(false)->live(),
            ]),
        ]);
    }

    /** @return array{headers: list<string>, rows: list<list<string|int|float>>} */
    abstract public function report(): array;

    public function getReportProperty(): array
    {
        return $this->report();
    }

    public function export(): StreamedResponse
    {
        $report = $this->report();
        $filename = str(static::class)->classBasename()->kebab() . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $report['headers']);
            foreach ($report['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function range(): array
    {
        return [
            \Illuminate\Support\Carbon::parse($this->data['from'] ?? now()->subMonths(5)->startOfMonth())->startOfDay(),
            \Illuminate\Support\Carbon::parse($this->data['until'] ?? now())->endOfDay(),
        ];
    }
}
