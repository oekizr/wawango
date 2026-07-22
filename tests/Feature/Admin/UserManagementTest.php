<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleName::Admin->value);

        return $admin;
    }

    private function pemesan(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::Pemesan->value);

        return $user;
    }

    public function test_admin_can_create_pemesan_user(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name' => 'Budi Santoso',
            'divisi' => 'Marketing',
            'lantai' => '2',
            'no_hp' => '081111111111',
            'email' => 'budi@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(RoleName::Pemesan->value));
    }

    public function test_admin_can_update_pemesan_user(): void
    {
        $user = $this->pemesan();

        $response = $this->actingAs($this->admin())->put(route('admin.users.update', $user), [
            'name' => 'Nama Baru',
            'divisi' => $user->divisi,
            'lantai' => $user->lantai,
            'no_hp' => $user->no_hp,
            'email' => $user->email,
            'status' => 'nonaktif',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSame('Nama Baru', $user->fresh()->name);
        $this->assertSame('nonaktif', $user->fresh()->status);
    }

    public function test_admin_can_delete_pemesan_user(): void
    {
        $user = $this->pemesan();

        $response = $this->actingAs($this->admin())->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSoftDeleted($user);
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $this->actingAs($this->pemesan())->get(route('admin.users.index'))->assertForbidden();
    }
}
