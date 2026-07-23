<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->provider()->exists();
    }

    public function create(User $user): bool
    {
        return $user->provider()->exists();
    }

    public function view(User $user, Store $store): bool
    {
        return $user->provider?->id === $store->provider_id;
    }

    public function update(User $user, Store $store): bool
    {
        return $user->provider?->id === $store->provider_id;
    }

    public function delete(User $user, Store $store): bool
    {
        return $user->provider?->id === $store->provider_id;
    }
}
