<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'nama_menu' => $this->nama_menu_snapshot,
            'price' => $this->price_snapshot,
            'qty' => $this->qty,
            'subtotal' => $this->subtotal,
            'note' => $this->note,
        ];
    }
}
