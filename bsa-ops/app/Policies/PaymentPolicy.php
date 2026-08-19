<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payment $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Payment $model): bool
    {
        return false;
    }

    public function delete(User $user, Payment $model): bool
    {
        return false;
    }

    public function restore(User $user, Payment $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Payment $model): bool
    {
        return false;
    }
}
