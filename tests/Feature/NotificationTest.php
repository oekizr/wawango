<?php

namespace Tests\Feature;

use App\Enums\RoleName;
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

    public function test_shared_notifications_prop_flags_read_vs_unread(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::Pemesan->value);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $user->notify(new OrderNotConfirmedNotification($order));
        $user->notify(new OrderNotConfirmedNotification($order));
        $user->unreadNotifications()->first()->markAsRead();

        $response = $this->actingAs($user)->get(route('pemesan.dashboard'));
        $props = $response->viewData('page')['props'];

        $this->assertSame(1, $props['notifications']['unread_count']);
        $readFlags = collect($props['notifications']['items'])->pluck('is_read')->sort()->values()->all();
        $this->assertSame([false, true], $readFlags);
    }
}
