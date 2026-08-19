<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\StockMovement;
use App\Models\User;

class StockMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function view(User $user, StockMovement $model): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function create(User $user): bool
    {
        return false; // append-only — written by InventoryService
    }

    public function update(User $user, StockMovement $model): bool
    {
        return false;
    }

    public function delete(User $user, StockMovement $model): bool
    {
        return false;
    }
}
