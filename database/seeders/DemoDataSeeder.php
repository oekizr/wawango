<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\MenuCategory;
use App\Models\Provider;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Provider Wawan: buka setiap hari kerja 08.00-09.00, sesuai contoh di spesifikasi.
        $wawan = User::factory()->create([
            'name' => 'Wawan Setiawan',
            'email' => 'wawan@wawango.test',
        ]);
        $wawan->assignRole(RoleName::PenyediaJasa->value);

        $providerWawan = Provider::factory()->create([
            'user_id' => $wawan->id,
            'is_active' => true,
        ]);

        foreach (range(1, 5) as $day) { // Senin-Jumat
            $providerWawan->schedules()->create([
                'day_of_week' => $day,
                'open_time' => '08:00:00',
                'close_time' => '09:00:00',
                'is_active' => true,
            ]);
        }

        $storeWarteg = Store::factory()->create([
            'provider_id' => $providerWawan->id,
            'nama_toko' => 'Warteg Bahari',
            'service_fee' => 10000,
        ]);

        $this->seedMenus($storeWarteg, [
            ['Nasi Uduk', 15000],
            ['Nasi Goreng', 18000],
            ['Ayam Goreng', 12000],
            ['Es Teh Manis', 5000],
            ['Kopi Hitam', 5000],
        ]);

        // Provider kedua: contoh langganan McD (biaya jasa lebih tinggi).
        $sari = User::factory()->create([
            'name' => 'Sari Wulandari',
            'email' => 'sari@wawango.test',
        ]);
        $sari->assignRole(RoleName::PenyediaJasa->value);

        $providerSari = Provider::factory()->create([
            'user_id' => $sari->id,
            'is_active' => false,
        ]);

        foreach (range(1, 5) as $day) {
            $providerSari->schedules()->create([
                'day_of_week' => $day,
                'open_time' => '11:30:00',
                'close_time' => '12:30:00',
                'is_active' => true,
            ]);
        }

        $storeMcd = Store::factory()->create([
            'provider_id' => $providerSari->id,
            'nama_toko' => "McDonald's",
            'service_fee' => 20000,
        ]);

        $this->seedMenus($storeMcd, [
            ['Paket Nasi Ayam', 35000],
            ['Burger Cheese', 28000],
            ['French Fries', 15000],
            ['Es Cola', 8000],
        ]);

        // Dua akun pemesan dengan email tetap, supaya gampang dipakai untuk testing.
        $pemesan1 = User::factory()->create([
            'name' => 'Budi Santoso',
            'divisi' => 'IT',
            'lantai' => '3',
            'no_hp' => '081234567801',
            'email' => 'pemesan1@wawango.test',
        ]);
        $pemesan1->assignRole(RoleName::Pemesan->value);

        $pemesan2 = User::factory()->create([
            'name' => 'Citra Lestari',
            'divisi' => 'Finance',
            'lantai' => '5',
            'no_hp' => '081234567802',
            'email' => 'pemesan2@wawango.test',
        ]);
        $pemesan2->assignRole(RoleName::Pemesan->value);

        // Beberapa pemesan tambahan (email acak) untuk variasi data uji.
        User::factory(3)
            ->create()
            ->each(fn (User $user) => $user->assignRole(RoleName::Pemesan->value));
    }

    /**
     * @param  array<int, array{0: string, 1: int}>  $menus
     */
    private function seedMenus(Store $store, array $menus): void
    {
        $category = MenuCategory::factory()->create([
            'store_id' => $store->id,
            'nama' => 'Menu Utama',
        ]);

        foreach ($menus as [$nama, $harga]) {
            $store->menus()->create([
                'menu_category_id' => $category->id,
                'nama' => $nama,
                'harga' => $harga,
                'status' => 'tersedia',
            ]);
        }
    }
}
