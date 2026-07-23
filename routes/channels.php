<?php

use App\Broadcasting\OrderChannel;
use App\Broadcasting\ProviderChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('orders.{orderId}', OrderChannel::class);

Broadcast::channel('providers.{providerId}', ProviderChannel::class);
