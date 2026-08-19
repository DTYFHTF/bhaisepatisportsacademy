<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // POS needs the catalog at every role
    }

    public function view(User $user, Product $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function update(User $user, Product $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function delete(User $user, Product $model): bool
    {
        return false;
    }
}
