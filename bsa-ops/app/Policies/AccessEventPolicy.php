<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\AccessEvent;
use App\Models\User;

class AccessEventPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function view(User $user, AccessEvent $model): bool
    {
        return $user->isAtLeast(StaffRole::Manager);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AccessEvent $model): bool
    {
        return false;
    }

    public function delete(User $user, AccessEvent $model): bool
    {
        return false;
    }

    public function restore(User $user, AccessEvent $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, AccessEvent $model): bool
    {
        return false;
    }
}
