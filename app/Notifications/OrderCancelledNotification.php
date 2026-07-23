<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order, private readonly ?string $reason = null, private readonly ?string $note = null) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $detail = $this->note ?? $this->reason;
        $message = "Order {$this->order->kode_order} dibatalkan.".($detail ? " Alasan: {$detail}" : '');

        return [
            'type' => 'order_cancelled',
            'order_id' => $this->order->id,
            'kode_order' => $this->order->kode_order,
            'reason' => $this->reason,
            'message' => $message,
        ];
    }
}
