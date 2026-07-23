<?php

namespace App\Http\Controllers\Pemesan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pemesan\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Store;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

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

    public function checkout(): Response
    {
        return Inertia::render('Pemesan/Checkout');
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $store = Store::findOrFail($request->validated('store_id'));

        $order = $this->checkoutService->checkout(
            $request->user(),
            $store,
            $request->validated('items'),
            $request->validated('payment_method'),
            $request->validated('notes'),
        );

        return redirect()->route('pemesan.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat, menunggu konfirmasi penyedia jasa.');
    }
}
