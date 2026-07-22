<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'nama_toko' => fake()->company(),
            'lokasi' => fake()->streetAddress(),
            'deskripsi' => fake()->sentence(),
            'service_fee' => fake()->randomElement([5000, 10000, 15000, 20000]),
            'status' => 'aktif',
        ];
    }
}
