<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;

class ExpireUnconfirmedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-unconfirmed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-cancel orders the provider did not confirm within 10 minutes of being placed';

    public function handle(OrderService $orderService): int
    {
        $expired = Order::where('status', OrderStatus::Menunggu->value)
            ->whereNull('confirmed_at')
            ->where('ordered_at', '<=', now()->subMinutes(10))
            ->get();

        foreach ($expired as $order) {
            $orderService->autoCancelUnconfirmed($order);
            $this->info("Order {$order->kode_order} dibatalkan otomatis (tidak dikonfirmasi).");
        }

        if ($expired->isEmpty()) {
            $this->info('Tidak ada order yang perlu dibatalkan.');
        }

        return self::SUCCESS;
    }
}
