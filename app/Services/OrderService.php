<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderNotConfirmedNotification;
use App\Notifications\OrderStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function updateStatus(Order $order, string $status, User $actor, ?string $note = null): Order
    {
        $current = OrderStatus::from($order->status);

        if ($current->isTerminal()) {
            throw ValidationException::withMessages([
                'status' => 'Order yang sudah '.$current->label().' tidak bisa diubah statusnya lagi.',
            ]);
        }

        return $this->transitionTo($order, $status, $actor, $note);
    }

    /**
     * Provider-facing action: advance the order exactly one step forward
     * (menunggu → diproses → dibelikan → diantar → selesai). Unlike the
     * admin's free-form updateStatus, providers cannot jump statuses.
     */
    public function advance(Order $order, User $actor, ?string $note = null): Order
    {
        $current = OrderStatus::from($order->status);
        $next = $current->next();

        if ($next === null) {
            throw ValidationException::withMessages([
                'status' => 'Order yang sudah '.$current->label().' tidak bisa dimajukan lagi.',
            ]);
        }

        // Leaving "menunggu" is the provider's confirmation that they can
        // fulfil the order — record it so the auto-cancel timeout knows
        // to leave this order alone.
        if ($current === OrderStatus::Menunggu && $order->confirmed_at === null) {
            $order->confirmed_at = now();
        }

        return $this->transitionTo($order, $next->value, $actor, $note);
    }

    /**
     * System-initiated cancellation for orders the provider never confirmed
     * within the allotted window. No acting user — triggered by the
     * orders:expire-unconfirmed scheduled command.
     */
    public function autoCancelUnconfirmed(Order $order): Order
    {
        $order = DB::transaction(function () use ($order) {
            $order->status = OrderStatus::Dibatalkan->value;
            $order->save();

            $order->statusHistories()->create([
                'changed_by' => null,
                'status' => OrderStatus::Dibatalkan->value,
                'note' => 'Dibatalkan otomatis: penyedia jasa tidak mengkonfirmasi pesanan dalam 10 menit.',
                'created_at' => now(),
            ]);

            $order->issues()->create([
                'reason' => 'tidak_dikonfirmasi',
                'note' => 'Penyedia jasa tidak mengkonfirmasi pesanan dalam 10 menit.',
                'created_by' => null,
                'created_at' => now(),
            ]);

            return $order->fresh();
        });

        event(new OrderStatusChanged($order));
        $order->user?->notify(new OrderNotConfirmedNotification($order));

        return $order;
    }

    public function cancel(Order $order, User $actor, ?string $reason = null, ?string $note = null): Order
    {
        $current = OrderStatus::from($order->status);

        if ($current->isTerminal()) {
            throw ValidationException::withMessages([
                'status' => 'Order yang sudah '.$current->label().' tidak bisa dibatalkan.',
            ]);
        }

        $order = DB::transaction(function () use ($order, $actor, $reason, $note) {
            $order->status = OrderStatus::Dibatalkan->value;
            $order->save();

            $order->statusHistories()->create([
                'changed_by' => $actor->id,
                'status' => OrderStatus::Dibatalkan->value,
                'note' => $note,
                'created_at' => now(),
            ]);

            if ($reason) {
                $order->issues()->create([
                    'reason' => $reason,
                    'note' => $note,
                    'created_by' => $actor->id,
                    'created_at' => now(),
                ]);
            }

            return $order->fresh();
        });

        event(new OrderStatusChanged($order));
        $order->user?->notify(new OrderCancelledNotification($order, $reason, $note));

        return $order;
    }

    private function transitionTo(Order $order, string $status, User $actor, ?string $note): Order
    {
        $order = DB::transaction(function () use ($order, $status, $actor, $note) {
            $order->status = $status;

            if ($status === OrderStatus::Selesai->value) {
                $order->completed_at = now();
            }

            $order->save();

            $order->statusHistories()->create([
                'changed_by' => $actor->id,
                'status' => $status,
                'note' => $note,
                'created_at' => now(),
            ]);

            return $order->fresh();
        });

        event(new OrderStatusChanged($order));

        // "menunggu" is the initial state (not a transition worth notifying
        // about) and "dibatalkan" is handled by cancel()/autoCancelUnconfirmed()
        // with their own notification carrying a reason.
        if (! in_array($status, [OrderStatus::Menunggu->value, OrderStatus::Dibatalkan->value], true)) {
            $order->user?->notify(new OrderStatusNotification($order, OrderStatus::from($status)));
        }

        return $order;
    }
}
