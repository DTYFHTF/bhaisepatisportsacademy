<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Carbon;

/**
 * Nepali fiscal year, admin-rolled (no Bikram Sambat calendar math).
 * The label ("2082-83") and its Gregorian start date live in settings;
 * an admin rolls them each Shrawan.
 */
class FiscalYear
{
    public function __construct(
        public readonly string $label,
        public readonly Carbon $startedOn,
    ) {
    }

    public static function current(): self
    {
        return new self(
            label: (string) Setting::get('current_fiscal_year', '2082-83'),
            startedOn: Carbon::parse(Setting::get('fiscal_year_started_on', '2026-07-16')),
        );
    }

    public static function label(): string
    {
        return static::current()->label;
    }
}
