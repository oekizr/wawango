<?php

namespace Tests\Feature\Provider;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function providerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::PenyediaJasa->value);
        Provider::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function orderFor(Provider $provider, string $status = 'menunggu'): Order
    {
        return Order::factory()->create([
            'provider_id' => $provider->id,
            'status' => $status,
        ]);
    }

    public function test_provider_can_view_own_order(): void
    {
        $user = $this->providerUser();
        $order = $this->orderFor($user->provider);

        $this->actingAs($user)->get(route('provider.orders.show', $order))->assertOk();
    }

    public function test_provider_cannot_view_another_providers_order(): void
    {
        $user = $this->providerUser();
        $otherOrder = Order::factory()->create();

        $this->actingAs($user)->get(route('provider.orders.show', $otherOrder))->assertForbidden();
    }

    public function test_provider_can_advance_own_order_one_step(): void
    {
        $user = $this->providerUser();
        $order = $this->orderFor($user->provider, 'menunggu');

        $response = $this->actingAs($user)->patch(route('provider.orders.advance', $order));

        $response->assertRedirect();
        $this->assertSame('diproses', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'diproses',
            'changed_by' => $user->id,
        ]);
    }

    public function test_provider_cannot_advance_completed_order(): void
    {
        $user = $this->providerUser();
        $order = $this->orderFor($user->provider, 'selesai');

        $response = $this->actingAs($user)->patch(route('provider.orders.advance', $order));

        $response->assertSessionHasErrors('status');
        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_provider_can_report_issue(): void
    {
        $user = $this->providerUser();
        $order = $this->orderFor($user->provider, 'diproses');

        $response = $this->actingAs($user)->patch(route('provider.orders.reportIssue', $order), [
            'reason' => 'menu_habis',
            'note' => 'Menu sudah habis.',
        ]);

        $response->assertRedirect();
        $this->assertSame('dibatalkan', $order->fresh()->status);
        $this->assertDatabaseHas('order_issues', [
            'order_id' => $order->id,
            'reason' => 'menu_habis',
        ]);
    }

    public function test_provider_cannot_advance_another_providers_order(): void
    {
        $user = $this->providerUser();
        $otherOrder = Order::factory()->create(['status' => 'menunggu']);

        $this->actingAs($user)->patch(route('provider.orders.advance', $otherOrder))->assertForbidden();
    }
}
