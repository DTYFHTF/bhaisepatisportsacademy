<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\CheckIn;
use App\Models\User;

class CheckInPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CheckIn $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, CheckIn $model): bool
    {
        return false;
    }

    public function delete(User $user, CheckIn $model): bool
    {
        return false;
    }

    public function restore(User $user, CheckIn $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, CheckIn $model): bool
    {
        return false;
    }
}
