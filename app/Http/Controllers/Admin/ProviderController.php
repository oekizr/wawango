<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProviderRequest;
use App\Http\Requests\Admin\UpdateProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use App\Services\ProviderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function __construct(private readonly ProviderService $providerService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Provider::class);

        $providers = Provider::query()
            ->with('user')
            ->withCount('stores')
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('is_active', $status === 'aktif'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Providers/Index', [
            'providers' => ProviderResource::collection($providers)->response()->getData(true),
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Provider::class);

        return Inertia::render('Admin/Providers/Create');
    }

    public function store(StoreProviderRequest $request): RedirectResponse
    {
        $this->providerService->create($request->validated());

        return redirect()->route('admin.providers.index')->with('success', 'Penyedia jasa berhasil ditambahkan.');
    }

    public function edit(Provider $provider): Response
    {
        $this->authorize('update', $provider);

        $provider->load('user', 'schedules');

        return Inertia::render('Admin/Providers/Edit', [
            'provider' => new ProviderResource($provider),
        ]);
    }

    public function update(UpdateProviderRequest $request, Provider $provider): RedirectResponse
    {
        $this->providerService->update($provider, $request->validated());

        return redirect()->route('admin.providers.index')->with('success', 'Penyedia jasa berhasil diperbarui.');
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        $this->authorize('delete', $provider);

        $this->providerService->delete($provider);

        return redirect()->route('admin.providers.index')->with('success', 'Penyedia jasa berhasil dihapus.');
    }
}
