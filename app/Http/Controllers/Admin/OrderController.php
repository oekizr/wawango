<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelOrderRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Provider;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderService $orderService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $orders = $this->orderRepository->paginateForAdmin($request->only(
            'search', 'status', 'divisi', 'provider_id', 'date_from', 'date_to'
        ));

        return Inertia::render('Admin/Orders/Index', [
            'orders' => OrderResource::collection($orders)->response()->getData(true),
            'filters' => $request->only('search', 'status', 'divisi', 'provider_id', 'date_from', 'date_to'),
            'divisions' => $this->orderRepository->distinctDivisions(),
            'providers' => Provider::with('user')->get()->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->user?->name,
            ]),
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load(['user', 'store', 'provider.user', 'items', 'statusHistories.changedBy', 'issues', 'payment.proofs']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => new OrderResource($order),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->updateStatus($order, $request->validated('status'), $request->user(), $request->validated('note'));

        return back()->with('success', 'Status order berhasil diperbarui.');
    }

    public function cancel(CancelOrderRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->cancel($order, $request->user(), $request->validated('reason'), $request->validated('note'));

        return back()->with('success', 'Order berhasil dibatalkan.');
    }
}
