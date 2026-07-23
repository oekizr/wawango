<?php

namespace App\Http\Controllers\Pemesan;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function show(Store $store): Response
    {
        abort_unless($store->status === 'aktif', 404);

        $store->load('provider.user');

        $menus = $store->menus()
            ->with('category')
            ->orderBy('nama')
            ->get()
            ->map(fn ($menu) => [
                'id' => $menu->id,
                'nama' => $menu->nama,
                'harga' => $menu->harga,
                'status' => $menu->status,
                'foto_url' => $menu->foto ? Storage::disk('public')->url($menu->foto) : null,
                'kategori' => $menu->category?->nama,
            ]);

        return Inertia::render('Pemesan/Stores/Show', [
            'store' => [
                'id' => $store->id,
                'nama_toko' => $store->nama_toko,
                'lokasi' => $store->lokasi,
                'deskripsi' => $store->deskripsi,
                'service_fee' => $store->service_fee,
                'provider_id' => $store->provider_id,
                'provider_name' => $store->provider?->user?->name,
                'is_open' => $store->provider?->isOpenNow() ?? false,
            ],
            'menus' => $menus,
        ]);
    }
}
