<?php

namespace Tests\Feature\Pemesan;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    use RefreshDatabase;

    private function pemesanUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::Pemesan->value);

        return $user;
    }

    public function test_pemesan_can_upload_proof_for_own_transfer_order(): void
    {
        Storage::fake('public');

        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['user_id' => $pemesan->id, 'payment_method' => 'transfer']);
        Payment::factory()->create(['order_id' => $order->id, 'method' => 'transfer', 'status' => 'pending']);

        $response = $this->actingAs($pemesan)->post(route('pemesan.orders.paymentProof.store', $order), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_proofs', ['payment_id' => $order->payment->id]);
    }

    public function test_pemesan_cannot_upload_proof_for_cash_order(): void
    {
        Storage::fake('public');

        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['user_id' => $pemesan->id, 'payment_method' => 'cash']);
        Payment::factory()->create(['order_id' => $order->id, 'method' => 'cash', 'status' => 'pending']);

        $this->actingAs($pemesan)->post(route('pemesan.orders.paymentProof.store', $order), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertForbidden();
    }

    public function test_pemesan_cannot_upload_proof_for_another_users_order(): void
    {
        Storage::fake('public');

        $order = Order::factory()->create(['payment_method' => 'transfer']);
        Payment::factory()->create(['order_id' => $order->id, 'method' => 'transfer', 'status' => 'pending']);

        $this->actingAs($this->pemesanUser())->post(route('pemesan.orders.paymentProof.store', $order), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertForbidden();
    }

    public function test_pemesan_cannot_upload_proof_after_payment_accepted(): void
    {
        Storage::fake('public');

        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['user_id' => $pemesan->id, 'payment_method' => 'transfer']);
        Payment::factory()->create(['order_id' => $order->id, 'method' => 'transfer', 'status' => 'diterima']);

        $this->actingAs($pemesan)->post(route('pemesan.orders.paymentProof.store', $order), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertForbidden();
    }

    public function test_reuploading_resets_rejected_payment_to_pending(): void
    {
        Storage::fake('public');

        $pemesan = $this->pemesanUser();
        $order = Order::factory()->create(['user_id' => $pemesan->id, 'payment_method' => 'transfer']);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'method' => 'transfer', 'status' => 'ditolak']);

        $this->actingAs($pemesan)->post(route('pemesan.orders.paymentProof.store', $order), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $this->assertSame('pending', $payment->fresh()->status);
    }
}
