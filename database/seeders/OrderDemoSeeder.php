<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderDemoSeeder extends Seeder
{
    /**
     * Status yang disebar ke order demo, mencakup seluruh siklus hidup order
     * supaya dashboard & manajemen order admin punya data nyata untuk diuji.
     *
     * @var array<int, string>
     */
    private array $statusPlan = [
        'menunggu', 'diproses', 'dibelikan', 'diantar',
        'selesai', 'selesai', 'selesai', 'selesai', 'dibatalkan',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::with('menus')->get();
        $pemesans = User::role(RoleName::Pemesan->value)->get();

        if ($stores->isEmpty() || $pemesans->isEmpty()) {
            return;
        }

        foreach ($this->statusPlan as $index => $status) {
            $store = $stores->random();
            $menus = $store->menus->count() > 0 ? $store->menus->random(min(2, $store->menus->count())) : collect();
            $pemesan = $pemesans->random();
            $orderedAt = now()->subDays(count($this->statusPlan) - $index)->setTime(random_int(7, 11), random_int(0, 59));

            $items = $menus->map(fn (Menu $menu) => [
                'menu_id' => $menu->id,
                'nama_menu_snapshot' => $menu->nama,
                'price_snapshot' => $menu->harga,
                'qty' => random_int(1, 2),
            ])->map(fn (array $item) => $item + ['subtotal' => $item['price_snapshot'] * $item['qty']]);

            $subtotal = (int) $items->sum('subtotal');
            $serviceFee = (int) $store->service_fee;

            $order = Order::create([
                'kode_order' => 'WG'.strtoupper(Str::random(8)),
                'user_id' => $pemesan->id,
                'store_id' => $store->id,
                'provider_id' => $store->provider_id,
                'status' => $status,
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'total' => $subtotal + $serviceFee,
                'payment_method' => collect(['cash', 'transfer', 'qris'])->random(),
                'notes' => null,
                'divisi_snapshot' => $pemesan->divisi,
                'lantai_snapshot' => $pemesan->lantai,
                'ordered_at' => $orderedAt,
                'completed_at' => $status === 'selesai' ? $orderedAt->copy()->addMinutes(45) : null,
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            $this->seedStatusHistory($order, $status, $orderedAt);

            $order->payment()->create([
                'method' => $order->payment_method,
                'status' => $status === 'selesai' ? 'diterima' : ($status === 'dibatalkan' ? 'ditolak' : 'pending'),
                'amount' => $order->total,
                'paid_at' => $status === 'selesai' ? $orderedAt->copy()->addMinutes(5) : null,
            ]);

            if ($status === 'dibatalkan') {
                $order->issues()->create([
                    'reason' => collect(['toko_tutup', 'menu_habis', 'barang_tidak_ada'])->random(),
                    'note' => 'Dibatalkan otomatis oleh data demo.',
                    'created_by' => null,
                    'created_at' => $orderedAt->copy()->addMinutes(10),
                ]);
            }
        }
    }

    private function seedStatusHistory(Order $order, string $finalStatus, \Illuminate\Support\Carbon $orderedAt): void
    {
        $sequence = match ($finalStatus) {
            'dibatalkan' => ['menunggu', 'dibatalkan'],
            default => array_slice(
                ['menunggu', 'diproses', 'dibelikan', 'diantar', 'selesai'],
                0,
                array_search($finalStatus, ['menunggu', 'diproses', 'dibelikan', 'diantar', 'selesai']) + 1
            ),
        };

        foreach ($sequence as $step => $status) {
            $order->statusHistories()->create([
                'changed_by' => null,
                'status' => $status,
                'note' => null,
                'created_at' => $orderedAt->copy()->addMinutes($step * 10),
            ]);
        }
    }
}
