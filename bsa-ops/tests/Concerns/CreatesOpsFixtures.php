<?php

namespace Tests\Concerns;

use App\Enums\StaffRole;
use App\Models\Department;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Setting;
use App\Models\User;

trait CreatesOpsFixtures
{
    protected function seedSettings(array $overrides = []): void
    {
        $defaults = [
            'tax_rate_percent' => 13,
            'current_fiscal_year' => '2082-83',
            'fiscal_year_started_on' => '2026-07-16',
            'dues_grace_days' => 7,
            'dues_block_threshold' => 200000,
            'member_code_prefix' => 'BSA',
        ];

        foreach ([...$defaults, ...$overrides] as $key => $value) {
            Setting::set($key, $value);
        }
    }

    protected function makeDepartment(string $code = 'GYM', string $name = 'Gym'): Department
    {
        return Department::create(['code' => $code, 'name' => $name]);
    }

    protected function makeMonthlyPlan(Department $department, array $overrides = []): MembershipPlan
    {
        $plan = MembershipPlan::create([
            'code' => $overrides['code'] ?? 'GYM-1M',
            'name' => 'Gym Monthly',
            'plan_type' => 'time_based',
            'interval_unit' => 'months',
            'interval_count' => 1,
            'price' => 350000,
            'admission_fee' => 100000,
            'is_taxable' => true,
            'price_includes_tax' => true,
            'freeze_allowance_days' => 7,
            ...$overrides,
        ]);

        $plan->departments()->attach($department->id);

        return $plan->load('departments');
    }

    protected function makePackPlan(Department $department, array $overrides = []): MembershipPlan
    {
        $plan = MembershipPlan::create([
            'code' => $overrides['code'] ?? 'POOL-P10',
            'name' => 'Pool 10-pack',
            'plan_type' => 'session_pack',
            'session_count' => 10,
            'validity_days' => 90,
            'price' => 350000,
            'admission_fee' => 0,
            'is_taxable' => false,
            ...$overrides,
        ]);

        $plan->departments()->attach($department->id);

        return $plan->load('departments');
    }

    protected function makeMember(array $overrides = []): Member
    {
        // Derived from the table, not a static counter — a static would
        // leak across tests in the same process and desynchronise member
        // codes (and therefore ZKTeco device PINs) from what tests expect.
        $n = Member::withTrashed()->count() + 1;

        return Member::create([
            'member_code' => sprintf('BSA-%05d', $n),
            'first_name' => 'Test',
            'last_name' => "Member{$n}",
            'phone' => '98410' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'joined_on' => today(),
            'status' => 'active',
            ...$overrides,
        ]);
    }

    protected function makeStaff(StaffRole $role = StaffRole::FrontDesk): User
    {
        return User::create([
            'name' => ucfirst($role->value),
            'email' => $role->value . '-' . uniqid() . '@bsa.test',
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
