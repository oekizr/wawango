<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Store;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MenuService
{
    public function create(Store $store, array $data): Menu
    {
        $menu = $store->menus()->create([
            'menu_category_id' => $data['menu_category_id'] ?? null,
            'nama' => $data['nama'],
            'harga' => $data['harga'],
            'status' => $data['status'] ?? 'tersedia',
        ]);

        $this->storeImage($menu, $data);

        return $menu->fresh();
    }

    public function update(Menu $menu, array $data): Menu
    {
        $menu->update([
            'menu_category_id' => $data['menu_category_id'] ?? null,
            'nama' => $data['nama'],
            'harga' => $data['harga'],
            'status' => $data['status'] ?? 'tersedia',
        ]);

        $this->storeImage($menu, $data);

        return $menu->fresh();
    }

    public function delete(Menu $menu): void
    {
        $menu->delete();
    }

    private function storeImage(Menu $menu, array $data): void
    {
        if (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            if ($menu->foto) {
                Storage::disk('public')->delete($menu->foto);
            }

            $menu->foto = $data['foto']->store("menus/{$menu->store_id}", 'public');
            $menu->save();
        }
    }
}
