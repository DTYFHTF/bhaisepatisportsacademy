<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\AccessCredential;
use App\Models\User;

class AccessCredentialPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AccessCredential $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AccessCredential $model): bool
    {
        return true;
    }

    public function delete(User $user, AccessCredential $model): bool
    {
        return false;
    }

    public function restore(User $user, AccessCredential $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, AccessCredential $model): bool
    {
        return false;
    }
}
