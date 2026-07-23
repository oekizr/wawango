<?php

namespace Tests\Feature;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireUnconfirmedOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_cancels_unconfirmed_order_past_ten_minutes(): void
    {
        $order = Order::factory()->create([
            'status' => 'menunggu',
            'confirmed_at' => null,
            'ordered_at' => now()->subMinutes(11),
        ]);

        $this->artisan('orders:expire-unconfirmed')->assertSuccessful();

        $order->refresh();
        $this->assertSame('dibatalkan', $order->status);
        $this->assertDatabaseHas('order_issues', [
            'order_id' => $order->id,
            'reason' => 'tidak_dikonfirmasi',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $order->user_id,
            'type' => \App\Notifications\OrderNotConfirmedNotification::class,
        ]);
    }

    public function test_does_not_cancel_order_within_ten_minutes(): void
    {
        $order = Order::factory()->create([
            'status' => 'menunggu',
            'confirmed_at' => null,
            'ordered_at' => now()->subMinutes(5),
        ]);

        $this->artisan('orders:expire-unconfirmed');

        $this->assertSame('menunggu', $order->fresh()->status);
    }

    public function test_does_not_cancel_confirmed_order(): void
    {
        $order = Order::factory()->create([
            'status' => 'menunggu',
            'confirmed_at' => now()->subMinutes(9),
            'ordered_at' => now()->subMinutes(11),
        ]);

        $this->artisan('orders:expire-unconfirmed');

        $this->assertSame('menunggu', $order->fresh()->status);
    }

    public function test_does_not_touch_orders_in_other_statuses(): void
    {
        $order = Order::factory()->create([
            'status' => 'diproses',
            'confirmed_at' => now()->subMinutes(11),
            'ordered_at' => now()->subMinutes(20),
        ]);

        $this->artisan('orders:expire-unconfirmed');

        $this->assertSame('diproses', $order->fresh()->status);
    }
}
