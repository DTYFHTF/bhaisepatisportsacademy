<?php

namespace Tests\Feature;

use App\Enums\CheckInSource;
use App\Enums\SubscriptionStatus;
use App\Services\CheckInService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
        $this->service = app(SubscriptionService::class);
    }

    public function test_monthly_subscription_term_is_computed_from_start(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();

        $sub = $this->service->subscribe($member, $plan);

        $this->assertSame(today()->toDateString(), $sub->starts_on->toDateString());
        $this->assertSame(today()->addDays(29)->toDateString(), $sub->ends_on->toDateString());
        $this->assertSame(SubscriptionStatus::Active, $sub->status);
    }

    public function test_renewal_chains_and_starts_after_current_term(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();

        $first = $this->service->subscribe($member, $plan);
        $second = $this->service->renew($first);

        $this->assertSame($first->id, $second->renewed_from_id);
        $this->assertSame(
            $first->ends_on->addDay()->toDateString(),
            $second->starts_on->toDateString(),
        );
    }

    public function test_freeze_is_capped_by_plan_allowance(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym); // 7 freeze days
        $member = $this->makeMember();
        $sub = $this->service->subscribe($member, $plan);

        $this->expectException(ValidationException::class);
        $this->service->freeze($sub, today(), today()->addDays(10)); // 11 days > 7
    }

    public function test_unfreeze_extends_term_by_days_actually_frozen(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        $sub = $this->service->subscribe($member, $plan);
        $originalEnd = $sub->ends_on->copy();

        $this->service->freeze($sub, today(), today()->addDays(4)); // planned 5 days
        $this->service->unfreeze($sub->fresh(), today()->addDays(4)); // lifted on schedule

        $sub->refresh();
        $this->assertSame(SubscriptionStatus::Active, $sub->status);
        $this->assertSame($originalEnd->addDays(5)->toDateString(), $sub->ends_on->toDateString());
    }

    public function test_pack_sessions_deplete_on_check_in_and_expiry_command_closes_it(): void
    {
        $pool = $this->makeDepartment('POOL', 'Pool');
        $plan = $this->makePackPlan($pool, ['session_count' => 2]);
        $member = $this->makeMember();
        $sub = $this->service->subscribe($member, $plan);
        $checkIns = app(CheckInService::class);

        $first = $checkIns->checkIn($member, $pool, CheckInSource::FrontDesk);
        $second = $checkIns->checkIn($member, $pool, CheckInSource::FrontDesk);

        $this->assertTrue($first->was_allowed && $first->session_consumed);
        $this->assertTrue($second->was_allowed);
        $this->assertSame(0, $sub->fresh()->sessions_remaining);

        // Third visit is refused and recorded as a denial.
        $third = $checkIns->checkIn($member, $pool, CheckInSource::FrontDesk);
        $this->assertFalse($third->was_allowed);

        // Nightly job flips the depleted pack to expired.
        $this->artisan('ops:expire-subscriptions')->assertSuccessful();
        $this->assertSame(SubscriptionStatus::Expired, $sub->fresh()->status);
    }

    public function test_expiry_command_rolls_up_member_status(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        $sub = $this->service->subscribe($member, $plan);
        $sub->update(['starts_on' => today()->subDays(60), 'ends_on' => today()->subDay()]);

        $this->artisan('ops:expire-subscriptions')->assertSuccessful();

        $this->assertSame(SubscriptionStatus::Expired, $sub->fresh()->status);
        $this->assertSame('expired', $member->fresh()->status->value);
    }

    public function test_age_gated_plan_rejects_out_of_range_member(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym, ['code' => 'GYM-STU', 'max_age' => 25]);
        $member = $this->makeMember(['date_of_birth' => today()->subYears(40)]);

        $this->expectException(ValidationException::class);
        $this->service->subscribe($member, $plan);
    }
}
