<?php

namespace Tests\Feature\Provider;

use App\Enums\RoleName;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_update_own_schedule(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::PenyediaJasa->value);
        $provider = Provider::factory()->create(['user_id' => $user->id]);

        $schedules = collect(range(0, 6))->map(fn ($day) => [
            'day_of_week' => $day,
            'is_active' => $day >= 1 && $day <= 5,
            'open_time' => '08:00',
            'close_time' => '09:00',
        ])->all();

        $response = $this->actingAs($user)->put(route('provider.schedule.update'), [
            'schedules' => $schedules,
        ]);

        $response->assertRedirect(route('provider.schedule.edit'));
        $this->assertCount(7, $provider->fresh()->schedules);
        $this->assertSame(5, $provider->fresh()->schedules()->where('is_active', true)->count());
    }
}
