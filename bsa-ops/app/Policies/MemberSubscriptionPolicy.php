<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\MemberSubscription;
use App\Models\User;

class MemberSubscriptionPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MemberSubscription $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MemberSubscription $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function delete(User $user, MemberSubscription $model): bool
    {
        return false;
    }

    public function restore(User $user, MemberSubscription $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, MemberSubscription $model): bool
    {
        return false;
    }
}
