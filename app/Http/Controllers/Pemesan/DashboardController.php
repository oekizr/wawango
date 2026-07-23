<?php

namespace App\Http\Controllers\Pemesan;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $activeStatuses = array_map(fn (OrderStatus $s) => $s->value, OrderStatus::activeStatuses());

        $ordersActive = Order::where('user_id', auth()->id())
            ->whereIn('status', $activeStatuses)
            ->count();

        return Inertia::render('Pemesan/Dashboard', [
            'stats' => ['orders_active' => $ordersActive],
        ]);
    }
}
