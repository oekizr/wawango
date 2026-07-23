<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * @param  array<int, array{menu_id: int, qty: int, note?: ?string}>  $items
     */
    public function checkout(User $pemesan, Store $store, array $items, string $paymentMethod, ?string $notes = null): Order
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

        return DB::transaction(function () use ($pemesan, $store, $lineItems, $subtotal, $serviceFee, $paymentMethod, $notes) {
            $order = Order::create([
                'kode_order' => 'WG'.strtoupper(Str::random(8)),
                'user_id' => $pemesan->id,
                'store_id' => $store->id,
                'provider_id' => $store->provider_id,
                'status' => OrderStatus::Menunggu->value,
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'total' => $subtotal + $serviceFee,
                'payment_method' => $paymentMethod,
                'notes' => $notes,
                'divisi_snapshot' => $pemesan->divisi,
                'lantai_snapshot' => $pemesan->lantai,
                'ordered_at' => now(),
            ]);

            foreach ($lineItems as $lineItem) {
                $order->items()->create($lineItem);
            }

            $order->payment()->create([
                'method' => $paymentMethod,
                'status' => 'pending',
                'amount' => $order->total,
            ]);

            $order->statusHistories()->create([
                'changed_by' => $pemesan->id,
                'status' => OrderStatus::Menunggu->value,
                'note' => null,
                'created_at' => now(),
            ]);

            return $order->fresh();
        });
    }
}
