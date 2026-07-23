<?php

namespace App\Http\Controllers;

use App\Events\OrderMessagePosted;
use App\Http\Requests\OrderMessageRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

class OrderMessageController extends Controller
{
    public function store(OrderMessageRequest $request, Order $order): RedirectResponse
    {
        $message = $order->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        event(new OrderMessagePosted($message));

        return back();
    }
}
