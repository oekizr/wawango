<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order, private readonly bool $accepted) {}

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
        $message = $this->accepted
            ? "Pembayaran untuk order {$this->order->kode_order} telah diterima."
            : "Pembayaran untuk order {$this->order->kode_order} ditolak, silakan upload ulang bukti pembayaran.";

        return [
            'type' => 'payment_verified',
            'order_id' => $this->order->id,
            'kode_order' => $this->order->kode_order,
            'accepted' => $this->accepted,
            'message' => $message,
        ];
    }
}
