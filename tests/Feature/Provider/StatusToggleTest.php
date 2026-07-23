<?php

namespace Tests\Feature\Provider;

use App\Enums\RoleName;
use App\Models\Provider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function providerWithMondaySchedule(): Provider
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::PenyediaJasa->value);

        $provider = Provider::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        $provider->schedules()->create([
            'day_of_week' => 1, // Senin
            'open_time' => '08:00:00',
            'close_time' => '09:00:00',
            'is_active' => true,
        ]);

        return $provider;
    }

    public function test_provider_can_close_early_within_schedule_window(): void
    {
        $provider = $this->providerWithMondaySchedule();
        Carbon::setTestNow(now()->next(Carbon::MONDAY)->setTime(8, 30));

        $this->assertTrue($provider->fresh()->isOpenNow());

        $this->actingAs($provider->user)->post(route('provider.status.toggle'))->assertRedirect();

        $this->assertFalse($provider->fresh()->isOpenNow());
        $this->assertNotNull($provider->fresh()->manual_close_date);
    }

    public function test_provider_can_reopen_after_closing_early(): void
    {
        $provider = $this->providerWithMondaySchedule();
        Carbon::setTestNow(now()->next(Carbon::MONDAY)->setTime(8, 30));

        $this->actingAs($provider->user)->post(route('provider.status.toggle')); // close
        $this->actingAs($provider->user)->post(route('provider.status.toggle')); // reopen

        $this->assertTrue($provider->fresh()->isOpenNow());
        $this->assertNull($provider->fresh()->manual_close_date);
    }

    public function test_provider_cannot_open_outside_schedule(): void
    {
        $provider = $this->providerWithMondaySchedule();
        Carbon::setTestNow(now()->next(Carbon::MONDAY)->setTime(20, 0));

        $this->assertFalse($provider->fresh()->isOpenNow());

        $response = $this->actingAs($provider->user)->post(route('provider.status.toggle'));

        $response->assertSessionHasErrors('status');
        $this->assertNull($provider->fresh()->manual_close_date);
    }
}
