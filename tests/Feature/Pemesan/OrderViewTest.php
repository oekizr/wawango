<?php

namespace Tests\Feature\Pemesan;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderViewTest extends TestCase
{
    use RefreshDatabase;

    private function pemesanUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::Pemesan->value);

        return $user;
    }

    public function test_pemesan_can_view_own_orders_index(): void
    {
        $user = $this->pemesanUser();
        Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('pemesan.orders.index'))->assertOk();
    }

    public function test_pemesan_can_view_own_order_detail(): void
    {
        $user = $this->pemesanUser();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'menunggu']);

        $this->actingAs($user)->get(route('pemesan.orders.show', $order))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Pemesan/Orders/Show')
                ->where('order.id', $order->id)
                ->where('order.status', 'menunggu'));
    }

    public function test_pemesan_cannot_view_another_pemesans_order(): void
    {
        $user = $this->pemesanUser();
        $otherOrder = Order::factory()->create();

        $this->actingAs($user)->get(route('pemesan.orders.show', $otherOrder))->assertForbidden();
    }
}
