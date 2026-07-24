<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\AdvanceOrderRequest;
use App\Http\Requests\Provider\ReportIssueRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
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

        $provider = auth()->user()->provider;

        $orders = $this->orderRepository->paginateForProvider(
            $provider->id,
            $request->only('status')
        );

        return Inertia::render('Provider/Orders/Index', [
            'orders' => OrderResource::collection($orders)->response()->getData(true),
            'filters' => $request->only('status'),
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load(['user', 'store', 'items', 'statusHistories.changedBy', 'issues', 'payment.proofs', 'messages.sender']);

        return Inertia::render('Provider/Orders/Show', [
            'order' => (new OrderResource($order))->resolve(),
        ]);
    }

    public function advance(AdvanceOrderRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->advance($order, $request->user(), $request->validated('note'));

        return back()->with('success', 'Status order berhasil dimajukan.');
    }

    public function reportIssue(ReportIssueRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->cancel($order, $request->user(), $request->validated('reason'), $request->validated('note'));

        return back()->with('success', 'Kendala pesanan berhasil dilaporkan.');
    }
}
