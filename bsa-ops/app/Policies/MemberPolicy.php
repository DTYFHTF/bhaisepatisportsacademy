<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Member;
use App\Models\User;

class MemberPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Member $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Member $model): bool
    {
        return true;
    }

    public function delete(User $user, Member $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function restore(User $user, Member $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function forceDelete(User $user, Member $model): bool
    {
        return false;
    }
}
