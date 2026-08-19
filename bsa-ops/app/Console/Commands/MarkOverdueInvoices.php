<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'ops:mark-overdue-invoices';

    protected $description = 'Flip issued / partially paid invoices past their due date to overdue';

    public function handle(): int
    {
        $count = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid])
            ->whereDate('due_date', '<', today())
            ->update(['status' => InvoiceStatus::Overdue]);

        $this->info("Marked {$count} invoices overdue.");

        return self::SUCCESS;
    }
}
