<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\AccessDevice;
use App\Models\User;

class AccessDevicePolicy
{

    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function view(User $user, AccessDevice $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::SuperAdmin);
    }

    public function update(User $user, AccessDevice $model): bool
    {
        return $user->isAtLeast(StaffRole::SuperAdmin);
    }

    public function delete(User $user, AccessDevice $model): bool
    {
        return $user->isAtLeast(StaffRole::SuperAdmin);
    }

    public function restore(User $user, AccessDevice $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, AccessDevice $model): bool
    {
        return false;
    }
}
