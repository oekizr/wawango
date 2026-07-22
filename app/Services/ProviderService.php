<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProviderService
{
    public function create(array $data): Provider
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'divisi' => $data['divisi'],
                'lantai' => $data['lantai'],
                'no_hp' => $data['no_hp'],
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]);

            $user->assignRole(RoleName::PenyediaJasa->value);

            $provider = Provider::create([
                'user_id' => $user->id,
                'divisi' => $data['divisi'],
                'lantai' => $data['lantai'],
                'no_hp' => $data['no_hp'],
                'nama_bank' => $data['nama_bank'] ?? null,
                'no_rekening' => $data['no_rekening'] ?? null,
                'nama_pemilik_rekening' => $data['nama_pemilik_rekening'] ?? null,
                'is_active' => $data['is_active'] ?? false,
            ]);

            $this->storeImages($provider, $data);
            $this->syncSchedules($provider, $data['schedules']);

            return $provider->fresh(['user', 'schedules']);
        });
    }

    public function update(Provider $provider, array $data): Provider
    {
        return DB::transaction(function () use ($provider, $data) {
            $userAttributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'divisi' => $data['divisi'],
                'lantai' => $data['lantai'],
                'no_hp' => $data['no_hp'],
            ];

            if (! empty($data['password'])) {
                $userAttributes['password'] = Hash::make($data['password']);
            }

            $provider->user->update($userAttributes);

            $provider->update([
                'divisi' => $data['divisi'],
                'lantai' => $data['lantai'],
                'no_hp' => $data['no_hp'],
                'nama_bank' => $data['nama_bank'] ?? null,
                'no_rekening' => $data['no_rekening'] ?? null,
                'nama_pemilik_rekening' => $data['nama_pemilik_rekening'] ?? null,
                'is_active' => $data['is_active'] ?? false,
            ]);

            $this->storeImages($provider, $data);
            $this->syncSchedules($provider, $data['schedules']);

            return $provider->fresh(['user', 'schedules']);
        });
    }

    public function delete(Provider $provider): void
    {
        DB::transaction(function () use ($provider) {
            $provider->user->update(['status' => 'nonaktif']);
            $provider->delete();
        });
    }

    private function storeImages(Provider $provider, array $data): void
    {
        if (isset($data['foto_profil']) && $data['foto_profil'] instanceof UploadedFile) {
            if ($provider->foto_profil) {
                Storage::disk('public')->delete($provider->foto_profil);
            }

            $provider->foto_profil = $data['foto_profil']->store("providers/{$provider->id}", 'public');
        }

        if (isset($data['qris_image']) && $data['qris_image'] instanceof UploadedFile) {
            if ($provider->qris_image) {
                Storage::disk('public')->delete($provider->qris_image);
            }

            $provider->qris_image = $data['qris_image']->store("providers/{$provider->id}", 'public');
        }

        if ($provider->isDirty()) {
            $provider->save();
        }
    }

    /**
     * @param  array<int, array{day_of_week: int, is_active?: bool, open_time?: ?string, close_time?: ?string}>  $schedules
     */
    private function syncSchedules(Provider $provider, array $schedules): void
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
