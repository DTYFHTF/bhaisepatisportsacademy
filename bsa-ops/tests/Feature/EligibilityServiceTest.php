<?php

namespace Tests\Feature;

use App\Enums\DenialReason;
use App\Enums\MemberStatus;
use App\Enums\SubscriptionStatus;
use App\Services\EligibilityService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class EligibilityServiceTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    private EligibilityService $eligibility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
        $this->eligibility = app(EligibilityService::class);
    }

    public function test_active_subscription_covering_department_is_allowed(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan);

        $result = $this->eligibility->check($member, $gym);

        $this->assertTrue($result->allowed);
        $this->assertNotNull($result->subscription);
    }

    public function test_blacklisted_member_is_denied_before_anything_else(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan);
        $member->update(['status' => MemberStatus::Blacklisted]);

        $result = $this->eligibility->check($member->fresh(), $gym);

        $this->assertFalse($result->allowed);
        $this->assertSame(DenialReason::MemberBlacklisted, $result->reason);
    }

    public function test_member_without_subscription_is_denied(): void
    {
        $gym = $this->makeDepartment();
        $member = $this->makeMember();

        $result = $this->eligibility->check($member, $gym);

        $this->assertSame(DenialReason::NoActiveSubscription, $result->reason);
    }

    public function test_department_not_covered(): void
    {
        $gym = $this->makeDepartment();
        $pool = $this->makeDepartment('POOL', 'Pool');
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan);

        $result = $this->eligibility->check($member, $pool);

        $this->assertSame(DenialReason::DepartmentNotCovered, $result->reason);
    }

    public function test_frozen_subscription_is_denied(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        $service = app(SubscriptionService::class);
        $sub = $service->subscribe($member, $plan);
        $service->freeze($sub, today(), today()->addDays(3));

        $result = $this->eligibility->check($member, $gym);

        $this->assertSame(DenialReason::SubscriptionFrozen, $result->reason);
    }

    public function test_date_expired_subscription_is_denied_even_before_cron_flips_status(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        $sub = app(SubscriptionService::class)->subscribe($member, $plan);
        // Simulate the term having ended yesterday while status is still active.
        $sub->update(['starts_on' => today()->subDays(40), 'ends_on' => today()->subDay()]);

        $result = $this->eligibility->check($member, $gym);

        $this->assertSame(DenialReason::SubscriptionExpired, $result->reason);
    }

    public function test_pack_with_no_sessions_left_is_denied(): void
    {
        $pool = $this->makeDepartment('POOL', 'Pool');
        $plan = $this->makePackPlan($pool);
        $member = $this->makeMember();
        $sub = app(SubscriptionService::class)->subscribe($member, $plan);
        $sub->update(['sessions_remaining' => 0]);

        $result = $this->eligibility->check($member, $pool);

        $this->assertSame(DenialReason::NoSessionsRemaining, $result->reason);
    }

    public function test_off_peak_plan_outside_window_is_denied(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym, [
            'code' => 'GYM-OP',
            'is_off_peak' => true,
            'off_peak_start' => '10:00:00',
            'off_peak_end' => '16:00:00',
        ]);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan);

        $denied = $this->eligibility->check($member, $gym, now()->setTime(18, 0));
        $allowed = $this->eligibility->check($member, $gym, now()->setTime(12, 0));

        $this->assertSame(DenialReason::OutsideOffPeakHours, $denied->reason);
        $this->assertTrue($allowed->allowed);
    }

    public function test_dues_over_threshold_past_grace_are_denied_but_within_grace_allowed(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym); // 4,500 gross > 2,000 threshold
        $member = $this->makeMember();
        $sub = app(SubscriptionService::class)->subscribe($member, $plan);
        $invoice = $sub->invoice()->first();

        // Invoice unpaid but still inside due date + grace → allowed.
        $this->assertTrue($this->eligibility->check($member, $gym)->allowed);

        // Push the due date past the grace window → denied.
        $invoice->update(['due_date' => today()->subDays(10)]);
        $result = $this->eligibility->check($member, $gym);

        $this->assertSame(DenialReason::OutstandingDues, $result->reason);
    }

    public function test_time_based_subscription_is_preferred_over_pack(): void
    {
        $gym = $this->makeDepartment();
        $monthly = $this->makeMonthlyPlan($gym);
        $pack = $this->makePackPlan($gym, ['code' => 'GYM-P10']);
        $member = $this->makeMember();
        $service = app(SubscriptionService::class);
        $service->subscribe($member, $pack);
        $service->subscribe($member, $monthly);

        $result = $this->eligibility->check($member, $gym);

        $this->assertTrue($result->allowed);
        $this->assertSame($monthly->id, $result->subscription->membership_plan_id);
    }

    public function test_pending_subscription_does_not_grant_access(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan, activateNow: false);

        $result = $this->eligibility->check($member, $gym);

        $this->assertSame(DenialReason::NoActiveSubscription, $result->reason);
    }
}
