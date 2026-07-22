<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'divisi' => fake()->randomElement(['IT', 'Finance', 'HR', 'Marketing', 'Operasional']),
            'lantai' => (string) fake()->numberBetween(1, 10),
            'no_hp' => fake()->numerify('08##########'),
            'foto_profil' => null,
            'qris_image' => null,
            'nama_bank' => fake()->randomElement(['BCA', 'BRI', 'BNI', 'Mandiri']),
            'no_rekening' => fake()->numerify('##########'),
            'nama_pemilik_rekening' => fake()->name(),
            'is_active' => true,
        ];
    }
}
