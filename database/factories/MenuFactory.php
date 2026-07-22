<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
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
            'menu_category_id' => null,
            'nama' => fake()->randomElement([
                'Nasi Uduk', 'Nasi Goreng', 'Mie Ayam', 'Es Teh Manis',
                'Kopi Hitam', 'Roti Bakar', 'Gorengan', 'Nasi Padang',
            ]),
            'harga' => fake()->numberBetween(5, 30) * 1000,
            'foto' => null,
            'status' => 'tersedia',
        ];
    }
}
