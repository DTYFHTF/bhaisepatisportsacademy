<?php

namespace App\Console\Commands;

use App\Models\SubscriptionFreeze;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ReleaseFreezes extends Command
{
    protected $signature = 'ops:release-freezes';

    protected $description = 'Lift freezes whose window has ended, extending each subscription by the frozen days';

    public function handle(SubscriptionService $subscriptions): int
    {
        $due = SubscriptionFreeze::query()
            ->whereNull('lifted_at')
            ->whereDate('ends_on', '<=', today())
            ->with('subscription')
            ->get();

        $lifted = 0;

        foreach ($due as $freeze) {
            try {
                $subscriptions->unfreeze($freeze->subscription);
                $lifted++;
            } catch (\Throwable $e) {
                $this->warn("Freeze {$freeze->id}: {$e->getMessage()}");
            }
        }

        $this->info("Lifted {$lifted} freezes.");

        return self::SUCCESS;
    }
}
