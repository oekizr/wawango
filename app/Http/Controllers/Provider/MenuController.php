<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreMenuRequest;
use App\Http\Requests\Provider\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\Store;
use App\Services\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(private readonly MenuService $menuService) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Menu::class);

        $storeIds = auth()->user()->provider->stores()->pluck('id');

        $menus = Menu::whereIn('store_id', $storeIds)
            ->with('store')
            ->latest()
            ->get()
            ->map(fn (Menu $menu) => [
                'id' => $menu->id,
                'nama' => $menu->nama,
                'harga' => $menu->harga,
                'status' => $menu->status,
                'toko' => $menu->store->nama_toko,
                'foto_url' => $menu->foto ? Storage::disk('public')->url($menu->foto) : null,
            ]);

        return Inertia::render('Provider/Menus/Index', [
            'menus' => $menus,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Menu::class);

        return Inertia::render('Provider/Menus/Create', [
            'stores' => $this->ownStores(),
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $store = Store::findOrFail($request->validated('store_id'));

        $this->menuService->create($store, $request->validated());

        return redirect()->route('provider.menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu): Response
    {
        $this->authorize('update', $menu);

        return Inertia::render('Provider/Menus/Edit', [
            'menu' => [
                'id' => $menu->id,
                'store_id' => $menu->store_id,
                'menu_category_id' => $menu->menu_category_id,
                'nama' => $menu->nama,
                'harga' => $menu->harga,
                'status' => $menu->status,
                'foto_url' => $menu->foto ? Storage::disk('public')->url($menu->foto) : null,
            ],
            'stores' => $this->ownStores(),
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $this->menuService->update($menu, $request->validated());

        return redirect()->route('provider.menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->authorize('delete', $menu);

        $this->menuService->delete($menu);

        return redirect()->route('provider.menus.index')->with('success', 'Menu berhasil dihapus.');
    }

    private function ownStores()
    {
        return auth()->user()->provider->stores()->get(['id', 'nama_toko']);
    }
}
