<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
    }

    /**
     * Our models use uuid primary keys, but spatie's default migration
     * types subject_id as a bigint. MySQL truncates a uuid written into
     * that column (sqlite does not), which would silently destroy the
     * money audit trail in production.
     */
    public function test_activity_log_stores_full_uuid_subject_ids(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();

        $subscription = app(SubscriptionService::class)->subscribe($member, $plan);
        $invoice = $subscription->invoice()->first();

        $logged = Activity::query()
            ->where('subject_type', Invoice::class)
            ->where('subject_id', $invoice->id)
            ->exists();

        $this->assertTrue($logged, 'Invoice activity should be findable by its full uuid.');

        // Every recorded subject id must survive the round trip intact.
        foreach (Activity::whereNotNull('subject_id')->pluck('subject_id') as $subjectId) {
            $this->assertMatchesRegularExpression(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
                (string) $subjectId,
                'subject_id was truncated — check the activity_log column type.',
            );
        }
    }

    public function test_money_movements_are_attributed_to_the_staff_member(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym, ['admission_fee' => 0]);
        $member = $this->makeMember();
        $accountant = $this->makeStaff(StaffRole::Accountant);

        $invoice = app(SubscriptionService::class)->subscribe($member, $plan)->invoice()->first();
        $this->actingAs($accountant);
        app(BillingService::class)->applyPayment($invoice, ['amount' => 350000, 'method' => 'cash'], $accountant);

        $this->assertTrue(
            Activity::query()->where('subject_type', Invoice::class)->where('subject_id', $invoice->id)->exists(),
            'Paying an invoice should leave an audit row against that invoice.',
        );
    }
}
