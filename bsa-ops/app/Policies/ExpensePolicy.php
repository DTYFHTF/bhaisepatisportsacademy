<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{

    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function view(User $user, Expense $model): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function update(User $user, Expense $model): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function delete(User $user, Expense $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function restore(User $user, Expense $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Expense $model): bool
    {
        return false;
    }
}
