<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Discount;
use App\Models\User;

class DiscountPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function view(User $user, Discount $model): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function update(User $user, Discount $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function delete(User $user, Discount $model): bool
    {
        return false;
    }

    public function restore(User $user, Discount $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Discount $model): bool
    {
        return false;
    }
}
