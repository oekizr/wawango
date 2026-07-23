<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderNotConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_not_confirmed',
            'order_id' => $this->order->id,
            'kode_order' => $this->order->kode_order,
            'provider_name' => $this->order->provider?->user?->name ?? 'Penyedia jasa',
            'message' => "{$this->order->provider?->user?->name} tidak mengkonfirmasi pesanan {$this->order->kode_order} dalam 10 menit, order dibatalkan otomatis.",
        ];
    }
}
