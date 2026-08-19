<?php

namespace App\Support;

/**
 * Paisa <-> display helpers. All storage is integer paisa.
 */
class Money
{
    public static function npr(?int $paisa, bool $decimals = false): string
    {
        if ($paisa === null) {
            return '—';
        }

        return 'NPR ' . number_format($paisa / 100, $decimals ? 2 : 0);
    }

    /**
     * Parse a rupee string/number from a form into paisa.
     */
    public static function toPaisa(string|int|float|null $rupees): int
    {
        return (int) round(((float) ($rupees ?? 0)) * 100);
    }
}
