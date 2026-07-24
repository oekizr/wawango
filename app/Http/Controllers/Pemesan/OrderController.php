<?php

namespace App\Http\Controllers\Pemesan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pemesan\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Provider;
use App\Models\Store;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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

        $order->load(['store', 'provider.user', 'items', 'statusHistories.changedBy', 'issues', 'payment.proofs', 'messages.sender']);

        return Inertia::render('Pemesan/Orders/Show', [
            'order' => (new OrderResource($order))->resolve(),
            'paymentInfo' => $this->presentPaymentInfo($order->provider),
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
            $request->validated('notes'),
        );

        return redirect()->route('pemesan.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat, menunggu konfirmasi penyedia jasa.');
    }

    /**
     * @return array<string, mixed>
     */
    private function presentPaymentInfo(?Provider $provider): array
    {
        return [
            'nama_bank' => $provider?->nama_bank,
            'no_rekening' => $provider?->no_rekening,
            'nama_pemilik_rekening' => $provider?->nama_pemilik_rekening,
            'qris_image_url' => $provider?->qris_image ? Storage::disk('public')->url($provider->qris_image) : null,
        ];
    }
}
