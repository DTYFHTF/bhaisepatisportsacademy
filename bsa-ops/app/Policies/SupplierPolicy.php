<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function view(User $user, Supplier $model): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function update(User $user, Supplier $model): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function delete(User $user, Supplier $model): bool
    {
        return false;
    }
}
