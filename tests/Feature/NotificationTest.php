<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderNotConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $user->notify(new OrderNotConfirmedNotification($order));

        $notification = $user->unreadNotifications()->first();

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id));

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $owner->id]);
        $owner->notify(new OrderNotConfirmedNotification($order));
        $notification = $owner->unreadNotifications()->first();

        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->post(route('notifications.read', $notification->id))
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }
}
