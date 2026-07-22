<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
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

        return DB::transaction(function () use ($order, $status, $actor, $note) {
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
    }

    public function cancel(Order $order, User $actor, ?string $reason = null, ?string $note = null): Order
    {
        $current = OrderStatus::from($order->status);

        if ($current->isTerminal()) {
            throw ValidationException::withMessages([
                'status' => 'Order yang sudah '.$current->label().' tidak bisa dibatalkan.',
            ]);
        }

        return DB::transaction(function () use ($order, $actor, $reason, $note) {
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
    }
}
