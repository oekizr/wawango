<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuCategory>
 */
class MenuCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'nama' => fake()->randomElement(['Makanan', 'Minuman', 'Cemilan']),
            'urutan' => 0,
        ];
    }
}
