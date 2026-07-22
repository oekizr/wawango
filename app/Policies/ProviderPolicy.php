<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Provider;
use App\Models\User;

class ProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }

    public function view(User $user, Provider $provider): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }

    public function update(User $user, Provider $provider): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }

    public function delete(User $user, Provider $provider): bool
    {
        return $user->hasRole(RoleName::Admin->value);
    }
}
