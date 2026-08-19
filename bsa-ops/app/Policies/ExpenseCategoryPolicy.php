<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\ExpenseCategory;
use App\Models\User;

class ExpenseCategoryPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function view(User $user, ExpenseCategory $model): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function update(User $user, ExpenseCategory $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function delete(User $user, ExpenseCategory $model): bool
    {
        return false;
    }

    public function restore(User $user, ExpenseCategory $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, ExpenseCategory $model): bool
    {
        return false;
    }
}
