<?php

namespace Tests\Feature\Provider;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\Provider;
use App\Models\User;
use App\Notifications\PaymentVerifiedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function providerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::PenyediaJasa->value);
        Provider::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_provider_can_accept_transfer_payment_with_proof(): void
    {
        Notification::fake();

        $provider = $this->providerUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'method' => 'transfer', 'status' => 'pending']);
        PaymentProof::factory()->create(['payment_id' => $payment->id]);

        $response = $this->actingAs($provider)->patch(route('provider.orders.payment.verify', $order), [
            'status' => 'diterima',
        ]);

        $response->assertRedirect();
        $this->assertSame('diterima', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        Notification::assertSentTo($order->user, PaymentVerifiedNotification::class);
    }

    public function test_provider_can_reject_payment(): void
    {
        Notification::fake();

        $provider = $this->providerUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'method' => 'transfer', 'status' => 'pending']);
        PaymentProof::factory()->create(['payment_id' => $payment->id]);

        $this->actingAs($provider)->patch(route('provider.orders.payment.verify', $order), [
            'status' => 'ditolak',
        ]);

        $this->assertSame('ditolak', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
        Notification::assertSentTo($order->user, PaymentVerifiedNotification::class);
    }

    public function test_provider_cannot_accept_transfer_payment_without_proof(): void
    {
        $provider = $this->providerUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'method' => 'transfer', 'status' => 'pending']);

        $response = $this->actingAs($provider)->patch(route('provider.orders.payment.verify', $order), [
            'status' => 'diterima',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_provider_can_accept_cash_payment_without_proof(): void
    {
        $provider = $this->providerUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'method' => 'cash', 'status' => 'pending']);

        $response = $this->actingAs($provider)->patch(route('provider.orders.payment.verify', $order), [
            'status' => 'diterima',
        ]);

        $response->assertRedirect();
        $this->assertSame('diterima', $payment->fresh()->status);
    }

    public function test_provider_cannot_verify_another_providers_order_payment(): void
    {
        $provider = $this->providerUser();
        $otherOrder = Order::factory()->create();
        Payment::factory()->create(['order_id' => $otherOrder->id, 'method' => 'cash', 'status' => 'pending']);

        $this->actingAs($provider)
            ->patch(route('provider.orders.payment.verify', $otherOrder), ['status' => 'diterima'])
            ->assertForbidden();
    }
}
