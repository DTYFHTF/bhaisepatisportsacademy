<?php

namespace App\Console\Commands;

use App\Enums\MemberStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Member;
use App\Models\MemberSubscription;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'ops:expire-subscriptions';

    protected $description = 'Expire subscriptions past their end date or out of sessions, then roll up member statuses';

    public function handle(): int
    {
        $expired = MemberSubscription::query()
            ->where('status', SubscriptionStatus::Active)
            ->where(fn ($q) => $q
                ->whereDate('ends_on', '<', today())
                ->orWhere(fn ($q) => $q->whereNotNull('sessions_total')->where('sessions_remaining', '<=', 0)))
            ->update(['status' => SubscriptionStatus::Expired]);

        // Roll up member.status from live subscriptions. Blacklist is sticky.
        $flipped = 0;

        Member::query()
            ->whereNot('status', MemberStatus::Blacklisted)
            ->chunkById(200, function ($members) use (&$flipped) {
                foreach ($members as $member) {
                    $hasLive = $member->subscriptions()
                        ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Frozen])
                        ->exists();

                    $target = $hasLive ? MemberStatus::Active : MemberStatus::Expired;

                    if ($member->status !== $target) {
                        $member->update(['status' => $target]);
                        $flipped++;
                    }
                }
            });

        $this->info("Expired {$expired} subscriptions; updated {$flipped} member statuses.");

        return self::SUCCESS;
    }
}
