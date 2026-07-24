<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_provider_can_send_image_with_message(): void
    {
        Storage::fake('public');

        $provider = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id, 'user_id' => $pemesan->id]);

        $response = $this->actingAs($provider)->post(route('provider.orders.messages.store', $order), [
            'body' => 'Ini foto barangnya.',
            'image' => UploadedFile::fake()->image('barang.jpg'),
        ]);

        $response->assertRedirect();
        $message = OrderMessage::where('order_id', $order->id)->first();
        $this->assertNotNull($message->image_path);
        Storage::disk('public')->assertExists($message->image_path);
    }

    public function test_pemesan_can_send_image_only_without_body(): void
    {
        Storage::fake('public');

        $provider = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id, 'user_id' => $pemesan->id]);

        $response = $this->actingAs($pemesan)->post(route('pemesan.orders.messages.store', $order), [
            'image' => UploadedFile::fake()->image('referensi.png'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('order_messages', [
            'order_id' => $order->id,
            'sender_id' => $pemesan->id,
            'body' => '',
        ]);
    }

    public function test_cannot_send_message_without_body_or_image(): void
    {
        $provider = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id, 'user_id' => $pemesan->id]);

        $this->actingAs($provider)
            ->post(route('provider.orders.messages.store', $order), [])
            ->assertSessionHasErrors(['body', 'image']);
    }

    public function test_cannot_send_non_image_file(): void
    {
        Storage::fake('public');

        $provider = $this->providerUser();
        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['provider_id' => $provider->provider->id, 'user_id' => $pemesan->id]);

        $this->actingAs($provider)
            ->post(route('provider.orders.messages.store', $order), [
                'image' => UploadedFile::fake()->create('dokumen.pdf', 100),
            ])
            ->assertSessionHasErrors('image');
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
