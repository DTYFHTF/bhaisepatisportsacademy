<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\MembershipPlan;
use App\Models\User;

class MembershipPlanPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MembershipPlan $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function update(User $user, MembershipPlan $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function delete(User $user, MembershipPlan $model): bool
    {
        return false;
    }

    public function restore(User $user, MembershipPlan $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, MembershipPlan $model): bool
    {
        return false;
    }
}
