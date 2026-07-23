<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;

class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->provider()->exists();
    }

    public function create(User $user): bool
    {
        return $user->provider()->exists();
    }

    public function view(User $user, Menu $menu): bool
    {
        return $user->provider?->id === $menu->store->provider_id;
    }

    public function update(User $user, Menu $menu): bool
    {
        return $user->provider?->id === $menu->store->provider_id;
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $user->provider?->id === $menu->store->provider_id;
    }
}
