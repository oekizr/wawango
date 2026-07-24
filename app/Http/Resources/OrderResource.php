<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_order' => $this->kode_order,
            'pemesan' => $this->user?->name,
            'pemesan_no_hp' => $this->user?->no_hp,
            'divisi' => $this->divisi_snapshot,
            'lantai' => $this->lantai_snapshot,
            'toko' => $this->store?->nama_toko,
            'provider' => $this->provider?->user?->name,
            'provider_id' => $this->provider_id,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'service_fee' => $this->service_fee,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'ordered_at' => $this->ordered_at,
            'confirmed_at' => $this->confirmed_at,
            'confirm_deadline' => $this->status === 'menunggu' ? $this->ordered_at?->copy()->addMinutes(10) : null,
            'completed_at' => $this->completed_at,
            'items' => $this->whenLoaded('items', fn () => OrderItemResource::collection($this->items)->resolve()),
            'status_histories' => $this->whenLoaded('statusHistories', fn () => $this->statusHistories
                ->sortBy('created_at')
                ->values()
                ->map(fn ($history) => [
                    'status' => $history->status,
                    'note' => $history->note,
                    'changed_by' => $history->changedBy?->name,
                    'created_at' => $history->created_at,
                ])),
            'issues' => $this->whenLoaded('issues', fn () => $this->issues->map(fn ($issue) => [
                'reason' => $issue->reason,
                'note' => $issue->note,
                'created_at' => $issue->created_at,
            ])),
            'payment' => $this->whenLoaded('payment', function () {
                if (! $this->payment) {
                    return null;
                }

                $latestProof = $this->payment->relationLoaded('proofs')
                    ? $this->payment->proofs->sortByDesc('uploaded_at')->first()
                    : null;

                return [
                    'method' => $this->payment->method,
                    'status' => $this->payment->status,
                    'amount' => $this->payment->amount,
                    'paid_at' => $this->payment->paid_at,
                    'proof_url' => $latestProof ? Storage::disk('public')->url($latestProof->image_path) : null,
                ];
            }),
            'messages' => $this->whenLoaded('messages', fn () => $this->messages
                ->sortBy('created_at')
                ->values()
                ->map(fn ($message) => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'image_url' => $message->image_path ? Storage::disk('public')->url($message->image_path) : null,
                    'sender_name' => $message->sender?->name ?? 'Pengguna',
                    'is_mine' => $message->sender_id === $request->user()?->id,
                    'created_at' => $message->created_at,
                ])),
        ];
    }
}
