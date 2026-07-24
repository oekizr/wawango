<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleName::Admin->value);

        return $admin;
    }

    public function test_admin_can_view_order_index(): void
    {
        Order::factory()->count(3)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk();
    }

    public function test_admin_can_view_order_detail(): void
    {
        $order = Order::factory()->create(['status' => 'menunggu']);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Orders/Show')
                ->where('order.id', $order->id)
                ->where('order.status', 'menunggu'));
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = $this->admin();
        $order = Order::factory()->create(['status' => 'menunggu']);

        $response = $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'diproses',
            'note' => 'Sedang diproses provider.',
        ]);

        $response->assertRedirect();
        $this->assertSame('diproses', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'diproses',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_cannot_update_status_of_terminal_order(): void
    {
        $order = Order::factory()->create(['status' => 'selesai']);

        $response = $this->actingAs($this->admin())->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'diproses',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_admin_can_cancel_order(): void
    {
        $admin = $this->admin();
        $order = Order::factory()->create(['status' => 'menunggu']);

        $response = $this->actingAs($admin)->patch(route('admin.orders.cancel', $order), [
            'reason' => 'menu_habis',
            'note' => 'Menu habis lebih awal.',
        ]);

        $response->assertRedirect();
        $this->assertSame('dibatalkan', $order->fresh()->status);
        $this->assertDatabaseHas('order_issues', [
            'order_id' => $order->id,
            'reason' => 'menu_habis',
        ]);
    }

    public function test_non_admin_cannot_manage_orders(): void
    {
        $pemesan = User::factory()->create();
        $pemesan->assignRole(RoleName::Pemesan->value);

        $order = Order::factory()->create();

        $this->actingAs($pemesan)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($pemesan)->get(route('admin.orders.show', $order))->assertForbidden();
    }
}
