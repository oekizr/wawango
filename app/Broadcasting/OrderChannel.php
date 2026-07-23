<?php

namespace App\Broadcasting;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class OrderChannel
{
    public function join(User $user, int|string $orderId): bool
    {
        $order = Order::find($orderId);

        return $order !== null && Gate::forUser($user)->check('chat', $order);
    }
}
