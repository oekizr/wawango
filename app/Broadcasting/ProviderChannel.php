<?php

namespace App\Broadcasting;

use App\Models\User;

class ProviderChannel
{
    public function join(User $user, int|string $providerId): bool
    {
        return $user->provider?->id === (int) $providerId;
    }
}
