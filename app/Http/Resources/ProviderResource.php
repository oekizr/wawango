<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProviderResource extends JsonResource
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
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'account_status' => $this->user?->status,
            'divisi' => $this->divisi,
            'lantai' => $this->lantai,
            'no_hp' => $this->no_hp,
            'foto_profil_url' => $this->foto_profil ? Storage::disk('public')->url($this->foto_profil) : null,
            'qris_image_url' => $this->qris_image ? Storage::disk('public')->url($this->qris_image) : null,
            'nama_bank' => $this->nama_bank,
            'no_rekening' => $this->no_rekening,
            'nama_pemilik_rekening' => $this->nama_pemilik_rekening,
            'is_active' => $this->is_active,
            'schedules' => $this->whenLoaded('schedules', fn () => $this->schedules
                ->sortBy('day_of_week')
                ->values()
                ->map(fn ($schedule) => [
                    'day_of_week' => $schedule->day_of_week,
                    'is_active' => $schedule->is_active,
                    'open_time' => substr($schedule->open_time, 0, 5),
                    'close_time' => substr($schedule->close_time, 0, 5),
                ])),
            'stores_count' => $this->whenCounted('stores'),
            'created_at' => $this->created_at,
        ];
    }
}
