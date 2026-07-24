<?php

namespace Tests\Feature;

use App\Broadcasting\OrderChannel;
use App\Broadcasting\ProviderChannel;
use App\Enums\RoleName;
use App\Events\NewOrderPlaced;
use App\Events\OrderMessagePosted;
use App\Events\OrderStatusChanged;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Provider;
use App\Models\Store;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderStatusNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function providerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::PenyediaJasa->value);
        Provider::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        return $user;
    }

    private function pemesanUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::Pemesan->value);

        return $user;
    }

    private function orderFor(Provider $provider, User $pemesan, string $status = 'menunggu'): Order
    {
        return Order::factory()->create([
            'provider_id' => $provider->id,
            'user_id' => $pemesan->id,
            'status' => $status,
        ]);
    }

    public function test_checkout_fires_new_order_event_and_notifies_provider(): void
    {
        Event::fake([NewOrderPlaced::class]);
        Notification::fake();

        Carbon::setTestNow(now()->next(Carbon::MONDAY)->setTime(8, 30));

        $providerUser = $this->providerUser();
        $provider = $providerUser->provider;
        $provider->schedules()->create([
            'day_of_week' => 1,
            'open_time' => '08:00:00',
            'close_time' => '09:00:00',
            'is_active' => true,
        ]);
        $store = Store::factory()->create(['provider_id' => $provider->id, 'status' => 'aktif']);
        $menu = Menu::factory()->create(['store_id' => $store->id, 'status' => 'tersedia']);

        $pemesan = $this->pemesanUser();

        $this->actingAs($pemesan)->post(route('pemesan.checkout.store'), [
            'store_id' => $store->id,
            'items' => [['menu_id' => $menu->id, 'qty' => 1]],
        ])->assertRedirect();

        Event::assertDispatched(NewOrderPlaced::class, fn ($event) => $event->order->provider_id === $provider->id);
        Notification::assertSentTo($providerUser, NewOrderNotification::class);
    }

    public function test_advancing_order_fires_status_changed_event_and_notifies_pemesan(): void
    {
        Event::fake([OrderStatusChanged::class]);
        Notification::fake();

        $providerUser = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = $this->orderFor($providerUser->provider, $pemesan, 'menunggu');

        $this->actingAs($providerUser)->patch(route('provider.orders.advance', $order))->assertRedirect();

        Event::assertDispatched(OrderStatusChanged::class, fn ($event) => $event->order->id === $order->id);
        Notification::assertSentTo($pemesan, OrderStatusNotification::class);
    }

    public function test_status_notification_carries_the_new_status(): void
    {
        Notification::fake();

        $providerUser = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = $this->orderFor($providerUser->provider, $pemesan, 'menunggu');

        $this->actingAs($providerUser)->patch(route('provider.orders.advance', $order));

        Notification::assertSentTo($pemesan, OrderStatusNotification::class, function ($notification) use ($pemesan) {
            return $notification->toArray($pemesan)['status'] === 'diproses';
        });
    }

    public function test_admin_cancelling_order_fires_status_changed_event_and_notifies_pemesan(): void
    {
        Event::fake([OrderStatusChanged::class]);
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole(RoleName::Admin->value);

        $providerUser = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = $this->orderFor($providerUser->provider, $pemesan, 'diproses');

        $this->actingAs($admin)->patch(route('admin.orders.cancel', $order), [
            'reason' => 'lainnya',
            'note' => 'Dibatalkan oleh admin.',
        ])->assertRedirect();

        Event::assertDispatched(OrderStatusChanged::class, fn ($event) => $event->order->id === $order->id);
        Notification::assertSentTo($pemesan, OrderCancelledNotification::class);
    }

    public function test_provider_reporting_issue_notifies_pemesan_of_cancellation(): void
    {
        Notification::fake();

        $providerUser = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = $this->orderFor($providerUser->provider, $pemesan, 'diproses');

        $this->actingAs($providerUser)->patch(route('provider.orders.reportIssue', $order), [
            'reason' => 'menu_habis',
            'note' => 'Menu sudah habis.',
        ])->assertRedirect();

        Notification::assertSentTo($pemesan, OrderCancelledNotification::class);
    }

    public function test_sending_chat_message_fires_message_posted_event(): void
    {
        Event::fake([OrderMessagePosted::class]);

        $providerUser = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = $this->orderFor($providerUser->provider, $pemesan);

        $this->actingAs($pemesan)->post(route('pemesan.orders.messages.store', $order), [
            'body' => 'Halo',
        ])->assertRedirect();

        Event::assertDispatched(OrderMessagePosted::class, fn ($event) => $event->message->order_id === $order->id);
    }

    // routes/channels.php registers these as class-based handlers (join()
    // method) specifically so the authorization rule can be unit tested
    // directly, without depending on a live broadcaster's socket-auth signing.

    public function test_order_channel_authorizes_owning_pemesan(): void
    {
        $providerUser = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = $this->orderFor($providerUser->provider, $pemesan);

        $this->assertTrue((new OrderChannel)->join($pemesan, $order->id));
    }

    public function test_order_channel_authorizes_owning_provider(): void
    {
        $providerUser = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = $this->orderFor($providerUser->provider, $pemesan);

        $this->assertTrue((new OrderChannel)->join($providerUser, $order->id));
    }

    public function test_order_channel_rejects_unrelated_pemesan(): void
    {
        $providerUser = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $otherPemesan = $this->pemesanUser();
        $order = $this->orderFor($providerUser->provider, $pemesan);

        $this->assertFalse((new OrderChannel)->join($otherPemesan, $order->id));
    }

    public function test_provider_channel_authorizes_owner(): void
    {
        $providerUser = $this->providerUser();

        $this->assertTrue((new ProviderChannel)->join($providerUser, $providerUser->provider->id));
    }

    public function test_provider_channel_rejects_other_provider(): void
    {
        $providerUser = $this->providerUser();
        $otherProviderUser = $this->providerUser();

        $this->assertFalse((new ProviderChannel)->join($otherProviderUser, $providerUser->provider->id));
    }
}
