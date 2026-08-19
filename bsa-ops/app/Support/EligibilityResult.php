<?php

namespace App\Support;

use App\Enums\DenialReason;
use App\Models\Member;
use App\Models\MemberSubscription;

/**
 * Outcome of an eligibility check — the single answer the kiosk,
 * admin panel, and door hardware all trust.
 */
readonly class EligibilityResult
{
    public function __construct(
        public bool $allowed,
        public ?DenialReason $reason = null,
        public ?MemberSubscription $subscription = null,
        public ?Member $member = null,
    ) {
    }

    public static function allow(Member $member, MemberSubscription $subscription): self
    {
        return new self(allowed: true, subscription: $subscription, member: $member);
    }

    public static function deny(DenialReason $reason, ?Member $member = null): self
    {
        return new self(allowed: false, reason: $reason, member: $member);
    }
}
