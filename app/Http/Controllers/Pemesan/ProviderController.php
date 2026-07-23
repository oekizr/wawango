<?php

namespace App\Http\Controllers\Pemesan;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function index(Request $request): Response
    {
        $providers = Provider::query()
            ->with(['user', 'schedules'])
            ->where('is_active', true)
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->get()
            ->map(fn (Provider $provider) => $this->presentProvider($provider));

        return Inertia::render('Pemesan/Providers/Index', [
            'providers' => $providers,
            'filters' => $request->only('search'),
        ]);
    }

    public function show(Provider $provider): Response
    {
        abort_unless($provider->is_active, 404);

        $provider->load(['user', 'schedules']);

        $stores = $provider->stores()
            ->where('status', 'aktif')
            ->withCount('menus')
            ->get()
            ->map(fn ($store) => [
                'id' => $store->id,
                'nama_toko' => $store->nama_toko,
                'lokasi' => $store->lokasi,
                'deskripsi' => $store->deskripsi,
                'service_fee' => $store->service_fee,
                'menus_count' => $store->menus_count,
            ]);

        return Inertia::render('Pemesan/Providers/Show', [
            'provider' => $this->presentProvider($provider),
            'stores' => $stores,
        ]);
    }

    private function presentProvider(Provider $provider): array
    {
        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return [
            'id' => $provider->id,
            'name' => $provider->user?->name,
            'is_open' => $provider->isOpenNow(),
            'schedules' => $provider->schedules
                ->where('is_active', true)
                ->sortBy('day_of_week')
                ->values()
                ->map(fn ($s) => [
                    'day' => $dayNames[$s->day_of_week],
                    'open_time' => substr($s->open_time, 0, 5),
                    'close_time' => substr($s->close_time, 0, 5),
                ]),
        ];
    }
}
