<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(5, 30) * 1000;
        $qty = fake()->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'menu_id' => Menu::factory(),
            'nama_menu_snapshot' => fake()->randomElement(['Nasi Uduk', 'Nasi Goreng', 'Es Teh Manis']),
            'price_snapshot' => $price,
            'qty' => $qty,
            'subtotal' => $price * $qty,
            'note' => null,
        ];
    }
}
