# WawanGo — Flow Aplikasi

## 1. Flow Autentikasi & Redirect per Role

```mermaid
flowchart TD
    A[User buka aplikasi] --> B{Sudah login?}
    B -- Tidak --> C[Halaman Login/Register]
    C --> D[Isi email, password, nama, divisi, lantai, no_hp]
    D --> E{Kredensial valid?}
    E -- Tidak --> C
    E -- Ya --> F{Cek role via spatie/permission}
    B -- Ya --> F
    F -- admin --> G[Dashboard Admin]
    F -- penyedia_jasa --> H[Dashboard Penyedia Jasa]
    F -- pemesan --> I[Dashboard Pemesan]
```

## 2. Flow Order Lifecycle (Pemesan ↔ Penyedia Jasa)

```mermaid
flowchart TD
    A[Pemesan cari provider yang Buka] --> B[Pilih toko]
    B --> C[Pilih menu + qty + catatan]
    C --> D[Checkout: subtotal + biaya jasa + grand total]
    D --> E[Pilih metode pembayaran]
    E -->|Transfer/QRIS| F[Upload bukti pembayaran]
    E -->|Cash| G[Submit order]
    F --> G
    G --> H[Order status: Menunggu]
    H --> I{Provider terima?}
    I -- Ya --> J[Diproses]
    I -- Tidak, ada kendala --> K[Provider pilih alasan: Toko Tutup / Menu Habis / Barang Tidak Ada / Cuaca / Lainnya]
    K --> L[Order status: Dibatalkan]
    L --> M[Notifikasi kendala ke Pemesan]
    J --> N[Dibelikan]
    N --> O[Dalam Pengantaran]
    O --> P[Provider klik Selesaikan]
    P --> Q[Order status: Selesai]
    Q --> R[Order masuk Histori kedua pihak]
```

Setiap perubahan status di atas dicatat ke `order_status_histories` dan dipancarkan sebagai event realtime (Reverb) sehingga Pemesan melihat timeline tracking tanpa reload — detail integrasi realtime dikerjakan di stage Realtime Notification.

## 3. Flow Status Layanan Provider (Buka/Tutup)

```mermaid
flowchart TD
    A[Provider set jadwal: hari + jam buka + jam tutup] --> B{Waktu sekarang dalam jadwal?}
    B -- Ya --> C[Status otomatis: Buka]
    B -- Tidak --> D[Status otomatis: Tutup]
    C --> E[Provider bisa override manual: Tutup lebih awal]
    D --> F[Provider tidak bisa override jadi Buka di luar jadwal]
    C --> G[Pemesan bisa checkout dari toko provider ini]
    D --> H[Toko provider disembunyikan/disabled di halaman Pemesan]
```

## 4. Flow Pembayaran

```mermaid
flowchart TD
    A[Checkout] --> B{Pilih metode}
    B -- Cash --> C[Payment status: pending, dibayar langsung saat serah terima]
    B -- Transfer --> D[Tampilkan Nama Bank, No Rekening, Nama Pemilik]
    B -- QRIS --> E[Tampilkan gambar QRIS milik provider]
    D --> F[Pemesan upload bukti transfer]
    E --> G[Pemesan upload bukti QRIS]
    F --> H[Payment status: pending]
    G --> H
    C --> I[Provider tandai Diterima saat serah terima]
    H --> J{Provider verifikasi bukti}
    J -- Valid --> K[Payment status: Diterima]
    J -- Tidak valid --> L[Payment status: Ditolak, notifikasi ke Pemesan]
    K --> M[Notifikasi: Pembayaran Diterima]
```

## 5. Flow Notifikasi Realtime (ringkasan, detail di stage Realtime)

```mermaid
sequenceDiagram
    participant P as Pemesan
    participant S as Server (Laravel + Reverb)
    participant W as Provider

    P->>S: Submit order
    S->>W: Broadcast event OrderCreated (badge + bunyi + popup)
    W->>S: Update status (Diproses/Dibelikan/Diantar)
    S->>P: Broadcast event OrderStatusUpdated (timeline update realtime)
    W->>S: Tandai Selesai
    S->>P: Broadcast event OrderCompleted + Laravel Notification "Order Selesai"
```
