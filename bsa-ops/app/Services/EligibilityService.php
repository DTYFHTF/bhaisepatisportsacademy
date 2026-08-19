<?php

namespace App\Services;

use App\Enums\DenialReason;
use App\Enums\MemberStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Department;
use App\Models\Member;
use App\Models\MemberSubscription;
use App\Models\Setting;
use App\Support\EligibilityResult;
use Carbon\CarbonInterface;

/**
 * Answers "may member X enter department Y right now?".
 *
 * Pure read — never writes. The single source of truth trusted by the
 * front-desk kiosk, the admin panel, and the door-controller API.
 *
 * Denial checks run in a fixed order so the reported reason is the most
 * fundamental problem, not an incidental one.
 */
class EligibilityService
{
    public function check(Member $member, Department $department, ?CarbonInterface $at = null): EligibilityResult
    {
        $at ??= now();

        // 1. Blacklist trumps everything.
        if ($member->status === MemberStatus::Blacklisted) {
            return EligibilityResult::deny(DenialReason::MemberBlacklisted, $member);
        }

        // Candidate subscriptions: active or frozen, already started.
        $subscriptions = $member->subscriptions()
            ->with('plan.departments')
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Frozen])
            ->whereDate('starts_on', '<=', $at)
            ->get();

        // 2. No live subscription at all.
        if ($subscriptions->isEmpty()) {
            return EligibilityResult::deny(DenialReason::NoActiveSubscription, $member);
        }

        // 3. Live subscriptions exist, but none covers this department.
        $covering = $subscriptions->filter(
            fn (MemberSubscription $s) => $s->plan->departments->contains('id', $department->id)
        );

        if ($covering->isEmpty()) {
            return EligibilityResult::deny(DenialReason::DepartmentNotCovered, $member);
        }

        // 4. Frozen.
        $unfrozen = $covering->reject(fn (MemberSubscription $s) => $s->isCurrentlyFrozen());

        if ($unfrozen->isEmpty()) {
            return EligibilityResult::deny(DenialReason::SubscriptionFrozen, $member);
        }

        // 5. Expired by date (belt-and-braces: the expiry command normally
        //    flips status, but a check between midnight and the cron run
        //    must still deny).
        $current = $unfrozen->filter(
            fn (MemberSubscription $s) => $s->ends_on === null || $s->ends_on->endOfDay()->gte($at)
        );

        if ($current->isEmpty()) {
            return EligibilityResult::deny(DenialReason::SubscriptionExpired, $member);
        }

        // 6. Session packs need sessions left.
        $withCapacity = $current->filter(
            fn (MemberSubscription $s) => ! $s->plan->isPack() || ($s->sessions_remaining ?? 0) > 0
        );

        if ($withCapacity->isEmpty()) {
            return EligibilityResult::deny(DenialReason::NoSessionsRemaining, $member);
        }

        // 7. Off-peak plans only admit inside their window.
        $inWindow = $withCapacity->filter(function (MemberSubscription $s) use ($at) {
            if (! $s->plan->is_off_peak) {
                return true;
            }

            $time = $at->format('H:i:s');

            return $time >= $s->plan->off_peak_start && $time <= $s->plan->off_peak_end;
        });

        if ($inWindow->isEmpty()) {
            return EligibilityResult::deny(DenialReason::OutsideOffPeakHours, $member);
        }

        // 8. Dues gate: block when outstanding balance exceeds the threshold
        //    AND the oldest open invoice is past its due date + grace days.
        $threshold = (int) Setting::get('dues_block_threshold', 0);
        $graceDays = (int) Setting::get('dues_grace_days', 7);

        if ($member->outstandingBalance() > $threshold) {
            $oldestDue = $member->invoices()
                ->where('balance', '>', 0)
                ->whereNull('voided_at')
                ->orderBy('due_date')
                ->value('due_date');

            if ($oldestDue !== null && $at->copy()->subDays($graceDays)->gt($oldestDue)) {
                return EligibilityResult::deny(DenialReason::OutstandingDues, $member);
            }
        }

        // Prefer time-based subscriptions so pack sessions aren't consumed
        // when an unlimited plan also covers the door.
        $chosen = $inWindow->sortBy(fn (MemberSubscription $s) => $s->plan->isPack() ? 1 : 0)->first();

        return EligibilityResult::allow($member, $chosen);
    }
}
