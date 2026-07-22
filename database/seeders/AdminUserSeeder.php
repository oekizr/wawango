<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@wawango.test'],
            [
                'name' => 'Admin WawanGo',
                'divisi' => 'Management',
                'lantai' => '1',
                'no_hp' => '081200000000',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'aktif',
            ]
        );

        $admin->syncRoles([RoleName::Admin->value]);
    }
}
