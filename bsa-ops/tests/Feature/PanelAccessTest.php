<?php

namespace Tests\Feature;

use App\Enums\ExpenseStatus;
use App\Enums\StaffRole;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
    }

    public function test_inactive_staff_cannot_access_the_panel(): void
    {
        $user = $this->makeStaff(StaffRole::Manager);
        $user->update(['is_active' => false]);

        $this->assertFalse($user->fresh()->canAccessPanel(filament()->getDefaultPanel()));
    }

    public function test_role_hierarchy_ranks_correctly(): void
    {
        $desk = $this->makeStaff(StaffRole::FrontDesk);
        $manager = $this->makeStaff(StaffRole::Manager);

        $this->assertTrue($manager->isAtLeast(StaffRole::Accountant));
        $this->assertFalse($desk->isAtLeast(StaffRole::Accountant));
        $this->assertTrue($desk->isAtLeast(StaffRole::FrontDesk));
    }

    public function test_front_desk_cannot_delete_members_but_manager_can(): void
    {
        $desk = $this->makeStaff(StaffRole::FrontDesk);
        $manager = $this->makeStaff(StaffRole::Manager);
        $member = $this->makeMember();

        $this->assertFalse($desk->can('delete', $member));
        $this->assertTrue($manager->can('delete', $member));
        $this->assertTrue($desk->can('create', \App\Models\Member::class));
    }

    public function test_expense_visibility_starts_at_accountant(): void
    {
        $desk = $this->makeStaff(StaffRole::FrontDesk);
        $accountant = $this->makeStaff(StaffRole::Accountant);

        $this->assertFalse($desk->can('viewAny', Expense::class));
        $this->assertTrue($accountant->can('viewAny', Expense::class));
    }

    public function test_user_management_is_super_admin_only(): void
    {
        $manager = $this->makeStaff(StaffRole::Manager);
        $admin = $this->makeStaff(StaffRole::SuperAdmin);

        $this->assertFalse($manager->can('viewAny', \App\Models\User::class));
        $this->assertTrue($admin->can('viewAny', \App\Models\User::class));
    }

    public function test_nobody_can_hard_update_invoices_through_the_panel(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        $invoice = app(SubscriptionService::class)->subscribe($member, $plan)->invoice()->first();

        $admin = $this->makeStaff(StaffRole::SuperAdmin);

        // Invoices are immutable ledger rows — mutations go through BillingService actions.
        $this->assertFalse($admin->can('update', $invoice));
        $this->assertFalse($admin->can('delete', $invoice));
    }

    public function test_login_screen_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }
}
