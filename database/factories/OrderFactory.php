<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Keep provider_id consistent with the assigned store's provider.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Order $order) {
            if (! $order->provider_id && $order->store_id) {
                $order->provider_id = Store::find($order->store_id)?->provider_id;
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10, 50) * 1000;
        $serviceFee = fake()->randomElement([5000, 10000, 15000, 20000]);

        return [
            'kode_order' => 'WG'.strtoupper(Str::random(8)),
            'user_id' => User::factory(),
            'store_id' => Store::factory(),
            'provider_id' => null,
            'status' => 'menunggu',
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'total' => $subtotal + $serviceFee,
            'notes' => null,
            'divisi_snapshot' => fake()->randomElement(['IT', 'Finance', 'HR', 'Marketing']),
            'lantai_snapshot' => (string) fake()->numberBetween(1, 10),
            'ordered_at' => now(),
        ];
    }
}
