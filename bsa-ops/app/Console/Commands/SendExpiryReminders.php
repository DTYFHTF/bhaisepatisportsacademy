<?php

namespace App\Console\Commands;

use App\Enums\StaffRole;
use App\Models\MemberSubscription;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class SendExpiryReminders extends Command
{
    protected $signature = 'ops:send-expiry-reminders';

    protected $description = 'Notify staff about subscriptions expiring within 7 days (SMS to members is a future integration)';

    public function handle(): int
    {
        $expiring = MemberSubscription::query()
            ->expiringWithin(7)
            ->with(['member', 'plan'])
            ->orderBy('ends_on')
            ->get();

        if ($expiring->isEmpty()) {
            $this->info('Nothing expiring within 7 days.');

            return self::SUCCESS;
        }

        $recipients = User::query()
            ->where('is_active', true)
            ->whereIn('role', [StaffRole::Manager, StaffRole::FrontDesk])
            ->get();

        $lines = $expiring->take(10)
            ->map(fn (MemberSubscription $s) => "{$s->member->full_name} — {$s->plan->name} ends {$s->ends_on->format('j M')}")
            ->implode("\n");

        foreach ($recipients as $user) {
            Notification::make()
                ->title("{$expiring->count()} subscriptions expire within 7 days")
                ->body($lines)
                ->warning()
                ->sendToDatabase($user);
        }

        $this->info("Notified {$recipients->count()} staff about {$expiring->count()} expiring subscriptions.");

        return self::SUCCESS;
    }
}
