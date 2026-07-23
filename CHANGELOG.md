# Changelog

Semua perubahan penting pada proyek WawanGo dicatat di sini. Format mengikuti
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) dan proyek ini
menggunakan [Semantic Versioning](https://semver.org/lang/id/) — setiap
"Stage" pengembangan dipetakan sebagai satu versi minor (0.x.0), perbaikan
kecil di antara stage sebagai versi patch.

## [0.6.1] - Fix: application timezone

### Diperbaiki
- `config('app.timezone')` diubah dari default `UTC` ke `Asia/Jakarta`
  (`APP_TIMEZONE` env var baru). Sebelumnya `now()` di server berjalan 7 jam
  di belakang waktu lokal, menyebabkan status buka/tutup penyedia jasa salah
  dibandingkan dengan jadwal yang dientri dalam waktu lokal (WIB).

## [0.6.0] - Stage 6: Realtime (Laravel Reverb)

### Ditambahkan
- Integrasi Laravel Reverb (WebSocket broadcasting) menggantikan polling.
- Notifikasi realtime "Order Baru" ke penyedia jasa (badge, bunyi, popup)
  serta notifikasi siklus hidup order (diproses, dibatalkan, selesai) ke
  pemesan, tanpa perlu reload halaman.
- Update status order & chat secara live di halaman detail order (Admin,
  Provider, Pemesan) via channel `orders.{id}`.
- Store Pinia `notifications` + komponen `Toast` untuk bell notifikasi live.
- `QUEUE_CONNECTION=sync` — broadcast notifikasi berjalan sinkron tanpa
  perlu proses `queue:work` tambahan.

## [0.5.0] - Stage 5: Modul Pembayaran

### Ditambahkan
- Upload bukti transfer/QRIS oleh pemesan.
- Verifikasi pembayaran (terima/tolak) oleh penyedia jasa.
- Re-upload bukti otomatis mereset status pembayaran yang ditolak ke pending.
- Notifikasi status pembayaran ke pemesan.

## [0.4.0] - Stage 4: Modul Pemesan & Konfirmasi Provider

### Ditambahkan
- Alur pemesan: browse toko/menu, keranjang (Pinia), checkout dengan
  re-validasi harga & ketersediaan di server.
- Konfirmasi wajib dari penyedia jasa dalam 10 menit sejak order dibuat;
  otomatis dibatalkan (`orders:expire-unconfirmed` via scheduler) jika lewat
  batas waktu, dengan notifikasi ke pemesan yang menyebut nama penyedia jasa.

## [0.3.0] - Stage 3: Modul Penyedia Jasa & Chat

### Ditambahkan
- Dashboard penyedia jasa: toggle buka/tutup, jadwal layanan, tutup lebih
  awal (`manual_close_date`).
- CRUD toko & menu milik penyedia jasa sendiri (dibatasi oleh policy).
- Kelola pesanan: konfirmasi/maju status satu langkah, lapor kendala.
- Fitur chat real-time (polling) antara penyedia jasa dan pemesan per order.
- Link WhatsApp (`wa.me`) untuk nomor HP pemesan di sisi penyedia jasa.

## [0.2.1] - Polish

### Ditambahkan
- Halaman utama (`/`) redirect langsung ke login/dashboard.
- Link "Daftar" di halaman Login menuju halaman Register.
- Komponen `BrandLogo` (logo WawanGo) di halaman Login/Register.
- 2 akun pemesan dengan email tetap di seeder untuk memudahkan testing.

## [0.2.0] - Stage 2: Modul Admin

### Ditambahkan
- Dashboard admin dengan grafik (custom SVG, brand-validated).
- CRUD Provider (termasuk pembuatan user & jadwal bersarang).
- CRUD User/Pemesan.
- Manajemen order dengan filter, untuk admin.

## [0.1.0] - Stage 1: Fondasi

### Ditambahkan
- Dokumen analisis, ERD, flow, wireframe, dan struktur folder.
- Migrasi, model, dan seeder untuk seluruh skema database inti.
- Autentikasi (Laravel Breeze, stack Vue/Inertia) dan RBAC
  (`spatie/laravel-permission`: admin, penyedia_jasa, pemesan).
