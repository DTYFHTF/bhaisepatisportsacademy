<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function view(User $user, Purchase $model): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function update(User $user, Purchase $model): bool
    {
        return false; // ledger — corrections via stock adjustment
    }

    public function delete(User $user, Purchase $model): bool
    {
        return false;
    }
}
