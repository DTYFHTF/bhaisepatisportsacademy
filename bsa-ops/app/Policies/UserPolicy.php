<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\User;

class UserPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::SuperAdmin);
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAtLeast(StaffRole::SuperAdmin);
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::SuperAdmin);
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAtLeast(StaffRole::SuperAdmin);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAtLeast(StaffRole::SuperAdmin);
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
