<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\VerifyPaymentRequest;
use App\Models\Order;
use App\Notifications\PaymentVerifiedNotification;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function verify(VerifyPaymentRequest $request, Order $order): RedirectResponse
    {
        $status = $request->validated('status');

        $order->payment->update([
            'status' => $status,
            'paid_at' => $status === 'diterima' ? now() : null,
        ]);

        $order->user?->notify(new PaymentVerifiedNotification($order, $status === 'diterima'));

        return back()->with('success', $status === 'diterima' ? 'Pembayaran ditandai diterima.' : 'Pembayaran ditolak.');
    }
}
