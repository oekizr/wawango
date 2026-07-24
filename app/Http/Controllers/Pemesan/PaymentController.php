<?php

namespace App\Http\Controllers\Pemesan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pemesan\ChoosePaymentMethodRequest;
use App\Http\Requests\Pemesan\StorePaymentProofRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function chooseMethod(ChoosePaymentMethodRequest $request, Order $order): RedirectResponse
    {
        $order->payment()->create([
            'method' => $request->validated('method'),
            'status' => 'pending',
            'amount' => $order->total,
        ]);

        return back()->with('success', 'Metode pembayaran berhasil dipilih.');
    }

    public function store(StorePaymentProofRequest $request, Order $order): RedirectResponse
    {
        $path = $request->file('bukti')->store("payment-proofs/{$order->id}", 'public');

        $order->payment->proofs()->create([
            'image_path' => $path,
            'uploaded_at' => now(),
        ]);

        // Reset a previously-rejected payment back to pending now that a new proof is in.
        if ($order->payment->status === 'ditolak') {
            $order->payment->update(['status' => 'pending']);
        }

        return back()->with('success', 'Bukti pembayaran berhasil diunggah, menunggu verifikasi penyedia jasa.');
    }
}
