<?php

namespace Tests\Feature\Pemesan;

use App\Enums\RoleName;
use App\Models\Menu;
use App\Models\Provider;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function pemesanUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::Pemesan->value);

        return $user;
    }

    private function openStoreWithMenu(): array
    {
        Carbon::setTestNow(now()->next(Carbon::MONDAY)->setTime(8, 30));

        $providerUser = User::factory()->create();
        $providerUser->assignRole(RoleName::PenyediaJasa->value);
        $provider = Provider::factory()->create(['user_id' => $providerUser->id, 'is_active' => true]);
        $provider->schedules()->create([
            'day_of_week' => 1,
            'open_time' => '08:00:00',
            'close_time' => '09:00:00',
            'is_active' => true,
        ]);

        $store = Store::factory()->create(['provider_id' => $provider->id, 'status' => 'aktif', 'service_fee' => 10000]);
        $menu = Menu::factory()->create(['store_id' => $store->id, 'harga' => 15000, 'status' => 'tersedia']);

        return [$store, $menu];
    }

    public function test_pemesan_can_checkout_from_open_store(): void
    {
        [$store, $menu] = $this->openStoreWithMenu();
        $pemesan = $this->pemesanUser();

        $response = $this->actingAs($pemesan)->post(route('pemesan.checkout.store'), [
            'store_id' => $store->id,
            'items' => [
                ['menu_id' => $menu->id, 'qty' => 2, 'note' => 'tidak pedas'],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $pemesan->id,
            'store_id' => $store->id,
            'status' => 'menunggu',
            'subtotal' => 30000,
            'service_fee' => 10000,
            'total' => 40000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'menu_id' => $menu->id,
            'qty' => 2,
            'price_snapshot' => 15000,
            'subtotal' => 30000,
        ]);
        // Payment method isn't chosen at checkout - only after the provider
        // confirms the order (see ChoosePaymentMethodTest).
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_checkout_rejected_when_store_closed(): void
    {
        [$store, $menu] = $this->openStoreWithMenu();
        Carbon::setTestNow(now()->setTime(20, 0)); // outside 08:00-09:00 window

        $response = $this->actingAs($this->pemesanUser())->post(route('pemesan.checkout.store'), [
            'store_id' => $store->id,
            'items' => [['menu_id' => $menu->id, 'qty' => 1]],
        ]);

        $response->assertSessionHasErrors('store_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejected_when_menu_habis(): void
    {
        [$store, $menu] = $this->openStoreWithMenu();
        $menu->update(['status' => 'habis']);

        $response = $this->actingAs($this->pemesanUser())->post(route('pemesan.checkout.store'), [
            'store_id' => $store->id,
            'items' => [['menu_id' => $menu->id, 'qty' => 1]],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('orders', 0);
    }
}
