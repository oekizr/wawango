<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentProof;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentProof>
 */
class PaymentProofFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'image_path' => 'payment-proofs/demo.jpg',
            'uploaded_at' => now(),
        ];
    }
}
