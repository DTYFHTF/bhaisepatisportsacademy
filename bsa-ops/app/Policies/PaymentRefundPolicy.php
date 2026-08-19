<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\PaymentRefund;
use App\Models\User;

class PaymentRefundPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function view(User $user, PaymentRefund $model): bool
    {
        return $user->isAtLeast(StaffRole::Accountant);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PaymentRefund $model): bool
    {
        return false;
    }

    public function delete(User $user, PaymentRefund $model): bool
    {
        return false;
    }

    public function restore(User $user, PaymentRefund $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, PaymentRefund $model): bool
    {
        return false;
    }
}
