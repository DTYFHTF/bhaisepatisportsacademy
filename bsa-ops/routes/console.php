<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Nightly housekeeping — runs via `php artisan schedule:run` from cron
 * (see docs/06-deployment.md).
 */
Schedule::command('ops:expire-subscriptions')->dailyAt('00:05');
Schedule::command('ops:release-freezes')->dailyAt('00:10');
Schedule::command('ops:mark-overdue-invoices')->dailyAt('00:15');
// Runs after the expiry sweep so lapsed members lose door access the same
// night; also re-runs hourly to pick up same-day renewals and freezes.
Schedule::command('ops:sync-access-devices')->dailyAt('00:20');
Schedule::command('ops:sync-access-devices')->hourly();
Schedule::command('ops:send-expiry-reminders')->dailyAt('08:00');
