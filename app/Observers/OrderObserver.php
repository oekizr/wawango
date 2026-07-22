<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\ActivityLogger;

class OrderObserver
{
    public function created(Order $order): void
    {
        ActivityLogger::log('created', $order, "Order baru: {$order->kode_order}");
    }

    public function updated(Order $order): void
    {
        $changes = array_diff(array_keys($order->getChanges()), ['updated_at']);

        if (empty($changes)) {
            return;
        }

        ActivityLogger::log('updated', $order, "Order {$order->kode_order} — field diubah: ".implode(', ', $changes));
    }

    public function deleted(Order $order): void
    {
        ActivityLogger::log('deleted', $order, "Order dihapus: {$order->kode_order}");
    }
}
