<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        // Admins see every order; providers may list orders too, but their
        // query (OrderRepository::paginateForProvider) is always pre-scoped
        // to their own provider_id, so granting this broadly is safe.
        return $user->hasRole(RoleName::Admin->value) || $user->provider()->exists();
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasRole(RoleName::Admin->value)
            || $this->isOwningProvider($user, $order)
            || $this->isOwningPemesan($user, $order);
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }

    public function advanceStatus(User $user, Order $order): bool
    {
        return $this->isOwningProvider($user, $order);
    }

    public function reportIssue(User $user, Order $order): bool
    {
        return $this->isOwningProvider($user, $order);
    }

    public function chat(User $user, Order $order): bool
    {
        return $user->hasRole(RoleName::Admin->value)
            || $this->isOwningProvider($user, $order)
            || $this->isOwningPemesan($user, $order);
    }

    private function isOwningProvider(User $user, Order $order): bool
    {
        return $user->provider?->id === $order->provider_id;
    }

    private function isOwningPemesan(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}
