<?php

namespace App\Observers;

use App\Models\Provider;
use App\Services\ActivityLogger;

class ProviderObserver
{
    public function created(Provider $provider): void
    {
        ActivityLogger::log('created', $provider, "Provider baru: {$provider->user?->name}");
    }

    public function updated(Provider $provider): void
    {
        $changes = array_diff(array_keys($provider->getChanges()), ['updated_at']);

        if (empty($changes)) {
            return;
        }

        ActivityLogger::log('updated', $provider, 'Field diubah: '.implode(', ', $changes));
    }

    public function deleted(Provider $provider): void
    {
        ActivityLogger::log('deleted', $provider, "Provider dihapus: {$provider->user?->name}");
    }
}
