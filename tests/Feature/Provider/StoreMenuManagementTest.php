<?php

namespace Tests\Feature\Provider;

use App\Enums\RoleName;
use App\Models\Menu;
use App\Models\Provider;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreMenuManagementTest extends TestCase
{
    use RefreshDatabase;

    private function providerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::PenyediaJasa->value);
        Provider::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_provider_can_create_own_store(): void
    {
        $user = $this->providerUser();

        $response = $this->actingAs($user)->post(route('provider.stores.store'), [
            'nama_toko' => 'Warteg Baru',
            'lokasi' => 'Lantai 1',
            'deskripsi' => 'Warteg enak',
            'service_fee' => 10000,
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('provider.stores.index'));
        $this->assertDatabaseHas('stores', [
            'nama_toko' => 'Warteg Baru',
            'provider_id' => $user->provider->id,
        ]);
    }

    public function test_provider_cannot_update_another_providers_store(): void
    {
        $user = $this->providerUser();
        $otherStore = Store::factory()->create();

        $this->actingAs($user)
            ->put(route('provider.stores.update', $otherStore), [
                'nama_toko' => 'Hacked',
                'service_fee' => 0,
                'status' => 'aktif',
            ])
            ->assertForbidden();
    }

    public function test_provider_can_create_menu_for_own_store(): void
    {
        $user = $this->providerUser();
        $store = Store::factory()->create(['provider_id' => $user->provider->id]);

        $response = $this->actingAs($user)->post(route('provider.menus.store'), [
            'store_id' => $store->id,
            'nama' => 'Nasi Goreng',
            'harga' => 15000,
            'status' => 'tersedia',
        ]);

        $response->assertRedirect(route('provider.menus.index'));
        $this->assertDatabaseHas('menus', ['nama' => 'Nasi Goreng', 'store_id' => $store->id]);
    }

    public function test_provider_cannot_create_menu_for_another_providers_store(): void
    {
        $user = $this->providerUser();
        $otherStore = Store::factory()->create();

        $response = $this->actingAs($user)->post(route('provider.menus.store'), [
            'store_id' => $otherStore->id,
            'nama' => 'Nasi Goreng',
            'harga' => 15000,
            'status' => 'tersedia',
        ]);

        $response->assertSessionHasErrors('store_id');
    }

    public function test_provider_cannot_update_menu_of_another_provider(): void
    {
        $user = $this->providerUser();
        $otherMenu = Menu::factory()->create();

        $this->actingAs($user)
            ->put(route('provider.menus.update', $otherMenu), [
                'nama' => 'Hacked',
                'harga' => 0,
                'status' => 'tersedia',
            ])
            ->assertForbidden();
    }
}
