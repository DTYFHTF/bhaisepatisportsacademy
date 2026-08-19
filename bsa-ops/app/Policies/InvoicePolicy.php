<?php

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invoice $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Invoice $model): bool
    {
        return false;
    }

    public function delete(User $user, Invoice $model): bool
    {
        return false;
    }

    public function restore(User $user, Invoice $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Invoice $model): bool
    {
        return false;
    }
}
