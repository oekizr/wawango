<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Events\NewOrderPlaced;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * @param  array<int, array{menu_id: int, qty: int, note?: ?string}>  $items
     */
    public function checkout(User $pemesan, Store $store, array $items, ?string $notes = null): Order
    {
        if ($store->status !== 'aktif' || ! $store->provider?->isOpenNow()) {
            throw ValidationException::withMessages([
                'store_id' => 'Toko ini sedang tutup, checkout tidak bisa dilanjutkan.',
            ]);
        }

        $menus = Menu::whereIn('id', collect($items)->pluck('menu_id'))
            ->where('store_id', $store->id)
            ->get()
            ->keyBy('id');

        $lineItems = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $menu = $menus->get($item['menu_id']);

            if (! $menu || $menu->status !== 'tersedia') {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu menu tidak tersedia lagi. Silakan periksa kembali keranjang Anda.',
                ]);
            }

            $qty = max(1, (int) $item['qty']);
            $lineSubtotal = $menu->harga * $qty;
            $subtotal += $lineSubtotal;

            $lineItems[] = [
                'menu_id' => $menu->id,
                'nama_menu_snapshot' => $menu->nama,
                'price_snapshot' => $menu->harga,
                'qty' => $qty,
                'subtotal' => $lineSubtotal,
                'note' => $item['note'] ?? null,
            ];
        }

        $serviceFee = (int) $store->service_fee;

        $order = DB::transaction(function () use ($pemesan, $store, $lineItems, $subtotal, $serviceFee, $notes) {
            $order = Order::create([
                'kode_order' => 'WG'.strtoupper(Str::random(8)),
                'user_id' => $pemesan->id,
                'store_id' => $store->id,
                'provider_id' => $store->provider_id,
                'status' => OrderStatus::Menunggu->value,
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'total' => $subtotal + $serviceFee,
                'notes' => $notes,
                'divisi_snapshot' => $pemesan->divisi,
                'lantai_snapshot' => $pemesan->lantai,
                'ordered_at' => now(),
            ]);

            foreach ($lineItems as $lineItem) {
                $order->items()->create($lineItem);
            }

            // Payment method is chosen by the pemesan later, once the
            // provider confirms the order — no point asking upfront if the
            // store might turn out to be closed or the menu unavailable.

            $order->statusHistories()->create([
                'changed_by' => $pemesan->id,
                'status' => OrderStatus::Menunggu->value,
                'note' => null,
                'created_at' => now(),
            ]);

            return $order->fresh();
        });

        event(new NewOrderPlaced($order));
        $order->provider?->user?->notify(new NewOrderNotification($order));

        return $order;
    }
}
