<?php

namespace App\Services;

use App\Enums\CheckInSource;
use App\Models\AccessDevice;
use App\Models\CheckIn;
use App\Models\Department;
use App\Models\Member;
use App\Models\MemberSubscription;
use App\Support\EligibilityResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    public function __construct(
        private readonly EligibilityService $eligibility,
    ) {
    }

    /**
     * Run the eligibility check and record the attempt — allowed or denied.
     * Pack sessions are consumed atomically.
     */
    public function checkIn(
        Member $member,
        Department $department,
        CheckInSource $source,
        ?AccessDevice $device = null,
        ?User $staff = null,
    ): CheckIn {
        $result = $this->eligibility->check($member, $department);

        return DB::transaction(function () use ($member, $department, $source, $device, $staff, $result) {
            $sessionConsumed = false;

            if ($result->allowed && $result->subscription->plan->isPack()) {
                // Atomic decrement guards against double-spend from
                // simultaneous door + kiosk check-ins.
                $updated = MemberSubscription::query()
                    ->whereKey($result->subscription->id)
                    ->where('sessions_remaining', '>', 0)
                    ->decrement('sessions_remaining');

                $sessionConsumed = $updated > 0;
            }

            return CheckIn::create([
                'member_id' => $member->id,
                'department_id' => $department->id,
                'member_subscription_id' => $result->subscription?->id,
                'checked_in_at' => now(),
                'source' => $source,
                'was_allowed' => $result->allowed,
                'denial_reason' => $result->reason,
                'session_consumed' => $sessionConsumed,
                'access_device_id' => $device?->id,
                'checked_in_by' => $staff?->id,
            ]);
        });
    }

    public function checkEligibility(Member $member, Department $department): EligibilityResult
    {
        return $this->eligibility->check($member, $department);
    }
}
