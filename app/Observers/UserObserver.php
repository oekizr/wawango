<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogger;

class UserObserver
{
    public function created(User $user): void
    {
        ActivityLogger::log('created', $user, "User baru: {$user->name} ({$user->email})");
    }

    public function updated(User $user): void
    {
        $changes = array_diff(array_keys($user->getChanges()), ['updated_at', 'remember_token']);

        if (empty($changes)) {
            return;
        }

        ActivityLogger::log('updated', $user, 'Field diubah: '.implode(', ', $changes));
    }

    public function deleted(User $user): void
    {
        ActivityLogger::log('deleted', $user, "User dihapus: {$user->name} ({$user->email})");
    }
}
