<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleName;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleName::Admin->value);

        return $admin;
    }

    private function scheduleInput(): array
    {
        return collect(range(0, 6))->map(fn ($day) => [
            'day_of_week' => $day,
            'is_active' => $day >= 1 && $day <= 5,
            'open_time' => '08:00',
            'close_time' => '09:00',
        ])->all();
    }

    public function test_admin_can_create_provider(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.providers.store'), [
            'name' => 'Wawan Tester',
            'email' => 'wawan.tester@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'divisi' => 'IT',
            'lantai' => '3',
            'no_hp' => '081234567890',
            'nama_bank' => 'BCA',
            'no_rekening' => '1234567890',
            'nama_pemilik_rekening' => 'Wawan Tester',
            'is_active' => true,
            'schedules' => $this->scheduleInput(),
        ]);

        $response->assertRedirect(route('admin.providers.index'));

        $user = User::where('email', 'wawan.tester@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(RoleName::PenyediaJasa->value));

        $provider = Provider::where('user_id', $user->id)->first();
        $this->assertNotNull($provider);
        $this->assertTrue((bool) $provider->is_active);
        $this->assertCount(7, $provider->schedules);
    }

    public function test_admin_can_view_provider_edit_page(): void
    {
        $provider = Provider::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.providers.edit', $provider))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Providers/Edit')
                ->where('provider.id', $provider->id)
                ->where('provider.name', $provider->user->name));
    }

    public function test_admin_can_update_provider(): void
    {
        $admin = $this->admin();
        $provider = Provider::factory()->create();
        $provider->schedules()->createMany($this->scheduleInput());

        $response = $this->actingAs($admin)->put(route('admin.providers.update', $provider), [
            'name' => 'Updated Name',
            'email' => $provider->user->email,
            'divisi' => 'Finance',
            'lantai' => '5',
            'no_hp' => '089999999999',
            'is_active' => false,
            'schedules' => $this->scheduleInput(),
        ]);

        $response->assertRedirect(route('admin.providers.index'));
        $this->assertSame('Updated Name', $provider->user->fresh()->name);
        $this->assertSame('Finance', $provider->fresh()->divisi);
        $this->assertFalse((bool) $provider->fresh()->is_active);
    }

    public function test_admin_can_delete_provider(): void
    {
        $admin = $this->admin();
        $provider = Provider::factory()->create();
        $provider->schedules()->createMany($this->scheduleInput());

        $response = $this->actingAs($admin)->delete(route('admin.providers.destroy', $provider));

        $response->assertRedirect(route('admin.providers.index'));
        $this->assertSoftDeleted($provider);
        $this->assertSame('nonaktif', $provider->user->fresh()->status);
    }

    public function test_non_admin_cannot_manage_providers(): void
    {
        $pemesan = User::factory()->create();
        $pemesan->assignRole(RoleName::Pemesan->value);

        $this->actingAs($pemesan)->get(route('admin.providers.index'))->assertForbidden();
        $this->actingAs($pemesan)->get(route('admin.providers.create'))->assertForbidden();
    }
}
