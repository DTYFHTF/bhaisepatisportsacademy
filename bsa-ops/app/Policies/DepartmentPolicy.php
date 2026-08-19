<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function view(User $user, Department $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::SuperAdmin);
    }

    public function update(User $user, Department $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function delete(User $user, Department $model): bool
    {
        return false;
    }

    public function restore(User $user, Department $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Department $model): bool
    {
        return false;
    }
}
