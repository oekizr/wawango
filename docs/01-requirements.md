# WawanGo — Analisis Kebutuhan

## 1. Latar Belakang

WawanGo adalah platform internal untuk pemesanan makanan, minuman, dan titip-beli barang antar rekan kerja. Berbeda dari GoFood/GrabFood, penyedia jasa bukan restoran melainkan rekan kerja (WawanGo Partner) yang bersedia membelikan pesanan saat mereka membuka layanan pada jam tertentu (mis. 08.00–09.00 untuk sarapan).

## 2. Masalah & Solusi

| # | Masalah | Solusi di WawanGo |
|---|---|---|
| 1 | Pembayaran sering membingungkan | Checkout terstruktur (subtotal, biaya jasa, grand total) + 3 metode pembayaran baku (Cash, Transfer, QRIS) dengan bukti upload & status pembayaran eksplisit |
| 2 | Toko terkadang tutup | `provider_schedules` + status Buka/Tutup realtime; toko yang tutup tidak bisa dipilih saat checkout |
| 3 | Menu habis | Field `status` pada menu (tersedia/habis); provider bisa nonaktifkan menu kapan saja |
| 4 | Penyedia jasa punya toko langganan berbeda | Setiap provider mengelola banyak `stores` miliknya sendiri, pemesan memilih provider lalu toko |
| 5 | Sulit tahu status pesanan | Tracking order dengan timeline status (`order_status_histories`) + notifikasi realtime |
| 6 | Sulit menghitung total pembayaran | Kalkulasi otomatis subtotal + biaya jasa per toko = grand total, ditampilkan di checkout |
| 7 | Tidak tahu provider sedang menerima order atau tidak | Status Buka/Tutup eksplisit, otomatis Tutup di luar jam layanan terjadwal |

## 3. Aktor & Role (RBAC)

- **Admin** — mengelola seluruh sistem: provider, user, order, laporan.
- **Penyedia Jasa (WawanGo Partner)** — mengelola toko, menu, jadwal layanan, dan memproses pesanan yang masuk.
- **Pemesan** — karyawan yang memesan makanan/minuman/titip-beli dari provider yang sedang buka.

Setiap role hanya melihat menu dan route yang menjadi hak aksesnya (route middleware + policy).

## 4. Ringkasan Modul

1. **Modul Admin** — dashboard analitik, CRUD provider, CRUD user, manajemen & histori seluruh order dengan filter.
2. **Modul Penyedia Jasa** — dashboard pendapatan, jadwal layanan, toggle Buka/Tutup, CRUD toko & menu, kelola pesanan masuk (proses → dibelikan → antar → selesai, atau tandai kendala), notifikasi order baru realtime.
3. **Modul Pemesan** — cari provider yang buka, pilih toko & menu, checkout, tracking order realtime, histori order.
4. **Modul Pembayaran** — Cash / Transfer Bank (upload bukti) / QRIS (upload bukti), tervalidasi di sisi provider.

## 5. Non-Functional Requirements

- **Realtime**: order baru & perubahan status terlihat tanpa reload (Laravel Reverb + Echo).
- **Mobile-first & PWA-ready**: dipakai mayoritas dari HP karyawan (Android & iPhone browser).
- **RBAC**: middleware + policy per role, tidak ada akses silang antar role.
- **Security**: CSRF, XSS, SQL injection protection (Eloquent + Form Request validation), rate limit login, audit log tiap aksi penting.
- **Auditability**: setiap perubahan status order & data master tercatat di `activity_logs`.
- **Extensibility**: struktur database sudah menyiapkan `provider_locations` untuk fitur live GPS tracking di versi berikutnya, tanpa implementasi penuh di tahap ini.

## 6. Lingkup Stage 1

Stage 1 hanya membangun fondasi: dokumentasi desain, struktur folder, migration, model, seeder, authentication (Breeze Vue/Inertia), dan RBAC dasar (role + middleware + layout placeholder per role). Modul bisnis (Admin/Provider/Pemesan/Payment), realtime, dan dashboard chart dikerjakan bertahap di stage-stage berikutnya.
