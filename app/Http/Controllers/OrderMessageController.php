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
        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store("chat-images/{$order->id}", 'public')
            : null;

        $message = $order->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->validated('body') ?? '',
            'image_path' => $imagePath,
        ]);

        event(new OrderMessagePosted($message));

        return back();
    }
}
