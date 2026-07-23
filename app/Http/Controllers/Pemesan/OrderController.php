<?php

namespace App\Http\Controllers\Pemesan;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['store', 'provider.user'])
            ->latest('ordered_at')
            ->paginate(10);

        return Inertia::render('Pemesan/Orders/Index', [
            'orders' => OrderResource::collection($orders)->response()->getData(true),
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load(['store', 'provider.user', 'items', 'statusHistories.changedBy', 'issues', 'payment', 'messages.sender']);

        return Inertia::render('Pemesan/Orders/Show', [
            'order' => new OrderResource($order),
        ]);
    }
}
