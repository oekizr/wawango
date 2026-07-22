<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderSchedule>
 */
class ProviderScheduleFactory extends Factory
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
            'day_of_week' => fake()->numberBetween(0, 6),
            'open_time' => '08:00:00',
            'close_time' => '09:00:00',
            'is_active' => true,
        ];
    }
}
