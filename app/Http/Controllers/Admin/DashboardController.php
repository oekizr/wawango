<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = today();

        $activeStatuses = array_map(fn (OrderStatus $s) => $s->value, OrderStatus::activeStatuses());

        $stats = [
            'total_providers' => Provider::count(),
            'total_users' => User::role(RoleName::Pemesan->value)->count(),
            'orders_today' => Order::whereDate('ordered_at', $today)->count(),
            'orders_active' => Order::whereIn('status', $activeStatuses)->count(),
            'orders_selesai' => Order::where('status', OrderStatus::Selesai->value)->count(),
            'total_pendapatan_jasa' => (int) Order::where('status', OrderStatus::Selesai->value)->sum('service_fee'),
        ];

        $days = collect(range(13, 0))->map(fn ($i) => $today->copy()->subDays($i));

        $chart = [
            'labels' => $days->map(fn ($d) => $d->format('d/m'))->all(),
            'orders' => $days->map(fn ($d) => Order::whereDate('ordered_at', $d)->count())->all(),
            'revenue' => $days->map(fn ($d) => (int) Order::whereDate('ordered_at', $d)
                ->where('status', OrderStatus::Selesai->value)
                ->sum('service_fee'))->all(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'chart' => $chart,
        ]);
    }
}
