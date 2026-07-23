<?php

namespace App\Services;

use App\Models\Provider;

class ScheduleService
{
    /**
     * @param  array<int, array{day_of_week: int, is_active?: bool, open_time?: ?string, close_time?: ?string}>  $schedules
     */
    public function sync(Provider $provider, array $schedules): void
    {
        $provider->schedules()->delete();

        foreach ($schedules as $schedule) {
            $provider->schedules()->create([
                'day_of_week' => $schedule['day_of_week'],
                'is_active' => $schedule['is_active'] ?? false,
                'open_time' => $schedule['open_time'] ?? '00:00',
                'close_time' => $schedule['close_time'] ?? '00:00',
            ]);
        }
    }
}
