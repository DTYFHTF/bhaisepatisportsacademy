<?php

namespace App\Services;

use App\Models\NumberSequence;
use App\Models\Setting;
use App\Support\FiscalYear;
use Illuminate\Support\Facades\DB;

/**
 * Race-safe sequential numbering. Rows are locked FOR UPDATE inside a
 * transaction (no-op on sqlite, which is single-writer anyway); the
 * unique index on the formatted numbers is the backstop.
 */
class NumberSequenceService
{
    public function next(string $key): int
    {
        return DB::transaction(function () use ($key) {
            $sequence = NumberSequence::query()
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = NumberSequence::create(['key' => $key, 'next_value' => 1]);
            }

            $value = $sequence->next_value;
            $sequence->update(['next_value' => $value + 1]);

            return $value;
        });
    }

    public function memberCode(): string
    {
        $prefix = (string) Setting::get('member_code_prefix', 'BSA');

        return sprintf('%s-%05d', $prefix, $this->next('member_code'));
    }

    public function invoiceNumber(): string
    {
        $fy = FiscalYear::label();

        return sprintf('INV-%s-%04d', $fy, $this->next("invoice:{$fy}"));
    }

    public function receiptNumber(): string
    {
        $fy = FiscalYear::label();

        return sprintf('RCP-%s-%04d', $fy, $this->next("receipt:{$fy}"));
    }

    public function voucherNumber(): string
    {
        $fy = FiscalYear::label();

        return sprintf('VCH-%s-%04d', $fy, $this->next("voucher:{$fy}"));
    }

    public function purchaseNumber(): string
    {
        $fy = FiscalYear::label();

        return sprintf('PUR-%s-%04d', $fy, $this->next("purchase:{$fy}"));
    }
}
