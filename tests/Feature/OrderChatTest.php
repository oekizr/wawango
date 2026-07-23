<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderChatTest extends TestCase
{
    use RefreshDatabase;

    private function providerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::PenyediaJasa->value);
        Provider::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function pemesanUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::Pemesan->value);

        return $user;
    }

    public function test_provider_owner_can_send_message(): void
    {
        $provider = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id, 'user_id' => $pemesan->id]);

        $response = $this->actingAs($provider)->post(route('provider.orders.messages.store', $order), [
            'body' => 'Halo, pesanan Anda sedang diproses.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('order_messages', [
            'order_id' => $order->id,
            'sender_id' => $provider->id,
            'body' => 'Halo, pesanan Anda sedang diproses.',
        ]);
    }

    public function test_pemesan_owner_can_send_message(): void
    {
        $provider = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id, 'user_id' => $pemesan->id]);

        $response = $this->actingAs($pemesan)->post(route('pemesan.orders.messages.store', $order), [
            'body' => 'Pesanan saya sudah sampai mana ya?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('order_messages', [
            'order_id' => $order->id,
            'sender_id' => $pemesan->id,
        ]);
    }

    public function test_unrelated_provider_cannot_send_message(): void
    {
        $provider = $this->providerUser();
        $otherProvider = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id, 'user_id' => $pemesan->id]);

        $this->actingAs($otherProvider)
            ->post(route('provider.orders.messages.store', $order), ['body' => 'Halo'])
            ->assertForbidden();
    }

    public function test_unrelated_pemesan_cannot_send_message(): void
    {
        $provider = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $otherPemesan = $this->pemesanUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id, 'user_id' => $pemesan->id]);

        $this->actingAs($otherPemesan)
            ->post(route('pemesan.orders.messages.store', $order), ['body' => 'Halo'])
            ->assertForbidden();
    }
}
