<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }
}
