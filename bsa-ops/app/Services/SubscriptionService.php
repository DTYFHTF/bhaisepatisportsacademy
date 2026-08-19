<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Discount;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\MemberSubscription;
use App\Models\SubscriptionFreeze;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function __construct(
        private readonly BillingService $billing,
    ) {
    }

    /**
     * Sell a plan to a member: snapshot pricing, compute the term,
     * raise the invoice. Admission fee applies on a member's first
     * subscription only.
     */
    public function subscribe(
        Member $member,
        MembershipPlan $plan,
        ?Discount $discount = null,
        ?CarbonInterface $startsOn = null,
        ?User $creator = null,
        bool $activateNow = true,
    ): MemberSubscription {
        $startsOn = ($startsOn ?? today())->toImmutable();

        $this->assertAgeAllowed($member, $plan);

        if ($discount && ! $discount->isUsable()) {
            throw ValidationException::withMessages(['discount' => 'This discount is no longer usable.']);
        }

        return DB::transaction(function () use ($member, $plan, $discount, $startsOn, $creator, $activateNow) {
            $isFirst = ! $member->subscriptions()->exists();
            $admissionFee = $isFirst ? $plan->admission_fee : 0;
            $discountAmount = $discount ? $discount->apply($plan->price) : 0;

            $duration = $plan->durationInDays();

            $subscription = MemberSubscription::create([
                'member_id' => $member->id,
                'membership_plan_id' => $plan->id,
                'price' => $plan->price,
                'admission_fee' => $admissionFee,
                'discount_id' => $discount?->id,
                'discount_amount' => $discountAmount,
                'starts_on' => $startsOn,
                'ends_on' => $duration ? $startsOn->addDays($duration)->subDay() : null,
                'sessions_total' => $plan->session_count,
                'sessions_remaining' => $plan->session_count,
                'status' => $activateNow ? SubscriptionStatus::Active : SubscriptionStatus::Pending,
                'activated_at' => $activateNow ? now() : null,
                'created_by' => $creator?->id,
            ]);

            $discount?->increment('used_count');

            $this->billing->createInvoiceForSubscription(
                $subscription,
                includeAdmission: $admissionFee > 0,
                creator: $creator,
            );

            return $subscription;
        });
    }

    /**
     * Renew into a fresh subscription chained to the old one.
     * Starts the day after the old term ends, or today if already lapsed.
     */
    public function renew(MemberSubscription $old, ?Discount $discount = null, ?User $creator = null): MemberSubscription
    {
        $plan = $old->plan;
        $startsOn = ($old->ends_on && $old->ends_on->isFuture())
            ? $old->ends_on->copy()->addDay()
            : today();

        $subscription = $this->subscribe(
            member: $old->member,
            plan: $plan,
            discount: $discount,
            startsOn: $startsOn,
            creator: $creator,
        );

        $subscription->update(['renewed_from_id' => $old->id]);

        return $subscription;
    }

    /**
     * Freeze pauses the clock. Planned days must fit within the plan's
     * remaining freeze allowance.
     */
    public function freeze(
        MemberSubscription $subscription,
        CarbonInterface $from,
        CarbonInterface $to,
        ?string $reason = null,
        ?User $creator = null,
    ): SubscriptionFreeze {
        if ($subscription->status !== SubscriptionStatus::Active) {
            throw ValidationException::withMessages(['subscription' => 'Only active subscriptions can be frozen.']);
        }

        $days = (int) $from->diffInDays($to) + 1;
        $allowance = $subscription->plan->freeze_allowance_days;
        $used = $subscription->totalFrozenDays();

        if ($days < 1) {
            throw ValidationException::withMessages(['freeze' => 'Freeze must be at least one day.']);
        }

        if ($used + $days > $allowance) {
            $left = max(0, $allowance - $used);
            throw ValidationException::withMessages([
                'freeze' => "Freeze allowance exceeded: {$left} of {$allowance} days remain on this plan.",
            ]);
        }

        return DB::transaction(function () use ($subscription, $from, $to, $days, $reason, $creator) {
            $freeze = SubscriptionFreeze::create([
                'member_subscription_id' => $subscription->id,
                'starts_on' => $from,
                'ends_on' => $to,
                'days_count' => $days,
                'reason' => $reason,
                'created_by' => $creator?->id,
            ]);

            $subscription->update(['status' => SubscriptionStatus::Frozen]);

            return $freeze;
        });
    }

    /**
     * Lift a freeze (manually or by the scheduler). The term is extended
     * by the days actually spent frozen.
     */
    public function unfreeze(MemberSubscription $subscription, ?CarbonInterface $on = null): MemberSubscription
    {
        $on ??= today();

        $freeze = $subscription->freezes()->whereNull('lifted_at')->latest('starts_on')->first();

        if (! $freeze || $subscription->status !== SubscriptionStatus::Frozen) {
            throw ValidationException::withMessages(['subscription' => 'This subscription is not frozen.']);
        }

        return DB::transaction(function () use ($subscription, $freeze, $on) {
            $effectiveEnd = $on->lt($freeze->ends_on) ? $on : $freeze->ends_on;
            $actualDays = max(1, (int) $freeze->starts_on->diffInDays($effectiveEnd) + 1);

            $freeze->update([
                'days_count' => $actualDays,
                'ends_on' => $effectiveEnd,
                'lifted_at' => now(),
            ]);

            $subscription->update([
                'status' => SubscriptionStatus::Active,
                'ends_on' => $subscription->ends_on?->copy()->addDays($actualDays),
            ]);

            return $subscription->refresh();
        });
    }

    public function cancel(MemberSubscription $subscription, string $reason, ?User $actor = null): MemberSubscription
    {
        if (in_array($subscription->status, [SubscriptionStatus::Cancelled, SubscriptionStatus::Expired], true)) {
            throw ValidationException::withMessages(['subscription' => 'This subscription is already closed.']);
        }

        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $subscription;
    }

    private function assertAgeAllowed(Member $member, MembershipPlan $plan): void
    {
        $age = $member->age;

        if ($age === null) {
            return;
        }

        if (($plan->min_age !== null && $age < $plan->min_age)
            || ($plan->max_age !== null && $age > $plan->max_age)) {
            throw ValidationException::withMessages([
                'plan' => "Member's age ({$age}) is outside this plan's range.",
            ]);
        }
    }
}
