<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreStoreRequest;
use App\Http\Requests\Provider\UpdateStoreRequest;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Store::class);

        $stores = auth()->user()->provider->stores()
            ->withCount('menus')
            ->latest()
            ->get()
            ->map(fn (Store $store) => [
                'id' => $store->id,
                'nama_toko' => $store->nama_toko,
                'lokasi' => $store->lokasi,
                'service_fee' => $store->service_fee,
                'status' => $store->status,
                'menus_count' => $store->menus_count,
            ]);

        return Inertia::render('Provider/Stores/Index', [
            'stores' => $stores,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Store::class);

        return Inertia::render('Provider/Stores/Create');
    }

    public function store(StoreStoreRequest $request): RedirectResponse
    {
        auth()->user()->provider->stores()->create($request->validated());

        return redirect()->route('provider.stores.index')->with('success', 'Toko berhasil ditambahkan.');
    }

    public function edit(Store $store): Response
    {
        $this->authorize('update', $store);

        return Inertia::render('Provider/Stores/Edit', [
            'store' => $store->only(['id', 'nama_toko', 'lokasi', 'deskripsi', 'service_fee', 'status']),
        ]);
    }

    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        $store->update($request->validated());

        return redirect()->route('provider.stores.index')->with('success', 'Toko berhasil diperbarui.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $this->authorize('delete', $store);

        $store->delete();

        return redirect()->route('provider.stores.index')->with('success', 'Toko berhasil dihapus.');
    }
}
