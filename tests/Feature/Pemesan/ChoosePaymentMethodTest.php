<?php

namespace Tests\Feature\Pemesan;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChoosePaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private function pemesanUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::Pemesan->value);

        return $user;
    }

    private function providerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::PenyediaJasa->value);
        Provider::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function orderFor(User $pemesan, Provider $provider, string $status): Order
    {
        return Order::factory()->create([
            'user_id' => $pemesan->id,
            'provider_id' => $provider->id,
            'status' => $status,
        ]);
    }

    public function test_pemesan_can_choose_payment_method_after_provider_confirms(): void
    {
        $pemesan = $this->pemesanUser();
        $provider = $this->providerUser();
        $order = $this->orderFor($pemesan, $provider->provider, 'diproses');

        $response = $this->actingAs($pemesan)->post(route('pemesan.orders.paymentMethod.store', $order), [
            'method' => 'transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'transfer',
            'status' => 'pending',
            'amount' => $order->total,
        ]);
    }

    public function test_pemesan_cannot_choose_payment_method_while_order_still_menunggu(): void
    {
        $pemesan = $this->pemesanUser();
        $provider = $this->providerUser();
        $order = $this->orderFor($pemesan, $provider->provider, 'menunggu');

        $this->actingAs($pemesan)
            ->post(route('pemesan.orders.paymentMethod.store', $order), ['method' => 'cash'])
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_pemesan_cannot_choose_payment_method_twice(): void
    {
        $pemesan = $this->pemesanUser();
        $provider = $this->providerUser();
        $order = $this->orderFor($pemesan, $provider->provider, 'diproses');
        Payment::factory()->create(['order_id' => $order->id, 'method' => 'cash']);

        $this->actingAs($pemesan)
            ->post(route('pemesan.orders.paymentMethod.store', $order), ['method' => 'transfer'])
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_pemesan_cannot_choose_payment_method_for_cancelled_order(): void
    {
        $pemesan = $this->pemesanUser();
        $provider = $this->providerUser();
        $order = $this->orderFor($pemesan, $provider->provider, 'dibatalkan');

        $this->actingAs($pemesan)
            ->post(route('pemesan.orders.paymentMethod.store', $order), ['method' => 'cash'])
            ->assertForbidden();
    }

    public function test_unrelated_pemesan_cannot_choose_payment_method(): void
    {
        $pemesan = $this->pemesanUser();
        $otherPemesan = $this->pemesanUser();
        $provider = $this->providerUser();
        $order = $this->orderFor($pemesan, $provider->provider, 'diproses');

        $this->actingAs($otherPemesan)
            ->post(route('pemesan.orders.paymentMethod.store', $order), ['method' => 'cash'])
            ->assertForbidden();
    }
}
