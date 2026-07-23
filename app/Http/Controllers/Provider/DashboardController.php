<?php

namespace App\Http\Controllers\Provider;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $provider = auth()->user()->provider;

        $activeStatuses = array_map(fn (OrderStatus $s) => $s->value, OrderStatus::activeStatuses());

        $stats = [
            'is_open' => $provider->isOpenNow(),
            'is_within_schedule' => $provider->isWithinScheduleNow(),
            'is_manually_closed' => $provider->isManuallyClosedToday(),
            'orders_active' => Order::where('provider_id', $provider->id)
                ->whereIn('status', $activeStatuses)
                ->count(),
            'revenue_today' => (int) Order::where('provider_id', $provider->id)
                ->where('status', OrderStatus::Selesai->value)
                ->whereDate('ordered_at', today())
                ->sum('service_fee'),
            'revenue_month' => (int) Order::where('provider_id', $provider->id)
                ->where('status', OrderStatus::Selesai->value)
                ->whereMonth('ordered_at', now()->month)
                ->whereYear('ordered_at', now()->year)
                ->sum('service_fee'),
        ];

        return Inertia::render('Provider/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
