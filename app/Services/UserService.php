<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'divisi' => $data['divisi'],
                'lantai' => $data['lantai'],
                'no_hp' => $data['no_hp'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => $data['status'],
                'email_verified_at' => now(),
            ]);

            $user->assignRole(RoleName::Pemesan->value);

            return $user;
        });
    }

    public function update(User $user, array $data): User
    {
        $attributes = [
            'name' => $data['name'],
            'divisi' => $data['divisi'],
            'lantai' => $data['lantai'],
            'no_hp' => $data['no_hp'],
            'email' => $data['email'],
            'status' => $data['status'],
        ];

        if (! empty($data['password'])) {
            $attributes['password'] = Hash::make($data['password']);
        }

        $user->update($attributes);

        return $user;
    }

    public function delete(User $user): void
    {
        $user->update(['status' => 'nonaktif']);
        $user->delete();
    }
}
