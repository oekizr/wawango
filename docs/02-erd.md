# WawanGo — Entity Relationship Diagram

## Diagram

```mermaid
erDiagram
    USERS ||--o| PROVIDERS : "punya profil"
    USERS ||--o{ ORDERS : "memesan"
    USERS ||--o{ ACTIVITY_LOGS : "melakukan aksi"
    USERS ||--o{ ORDER_STATUS_HISTORIES : "mengubah status"

    PROVIDERS ||--o{ PROVIDER_SCHEDULES : "punya jadwal"
    PROVIDERS ||--o| PROVIDER_LOCATIONS : "punya lokasi"
    PROVIDERS ||--o{ STORES : "mengelola"
    PROVIDERS ||--o{ ORDERS : "menerima"

    STORES ||--o{ MENU_CATEGORIES : "punya kategori"
    STORES ||--o{ MENUS : "menjual"
    STORES ||--o{ ORDERS : "dipesan dari"

    MENU_CATEGORIES ||--o{ MENUS : "mengelompokkan"

    ORDERS ||--o{ ORDER_ITEMS : "berisi"
    ORDERS ||--o{ ORDER_STATUS_HISTORIES : "riwayat status"
    ORDERS ||--o{ ORDER_ISSUES : "kendala"
    ORDERS ||--o| PAYMENTS : "dibayar via"

    MENUS ||--o{ ORDER_ITEMS : "dipesan sebagai"

    PAYMENTS ||--o{ PAYMENT_PROOFS : "bukti bayar"

    USERS {
        bigint id PK
        string nama
        string divisi
        string lantai
        string no_hp
        string email UK
        string password
        enum status "aktif, nonaktif"
        timestamp email_verified_at
        timestamp deleted_at
    }

    PROVIDERS {
        bigint id PK
        bigint user_id FK
        string divisi
        string lantai
        string no_hp
        string foto_profil
        string qris_image
        string nama_bank
        string no_rekening
        string nama_pemilik_rekening
        boolean is_active
        timestamp deleted_at
    }

    PROVIDER_SCHEDULES {
        bigint id PK
        bigint provider_id FK
        tinyint day_of_week "0=Minggu..6=Sabtu"
        time open_time
        time close_time
        boolean is_active
    }

    PROVIDER_LOCATIONS {
        bigint id PK
        bigint provider_id FK
        decimal latitude
        decimal longitude
        timestamp located_at
    }

    STORES {
        bigint id PK
        bigint provider_id FK
        string nama_toko
        string lokasi
        text deskripsi
        decimal service_fee
        enum status "aktif, nonaktif"
        timestamp deleted_at
    }

    MENU_CATEGORIES {
        bigint id PK
        bigint store_id FK
        string nama
        int urutan
    }

    MENUS {
        bigint id PK
        bigint store_id FK
        bigint menu_category_id FK
        string nama
        decimal harga
        string foto
        enum status "tersedia, habis"
        timestamp deleted_at
    }

    ORDERS {
        bigint id PK
        string kode_order UK
        bigint user_id FK "pemesan"
        bigint store_id FK
        bigint provider_id FK
        enum status "menunggu, diproses, dibelikan, diantar, selesai, dibatalkan"
        decimal subtotal
        decimal service_fee
        decimal total
        enum payment_method "cash, transfer, qris"
        text notes
        string divisi_snapshot
        string lantai_snapshot
        timestamp ordered_at
        timestamp completed_at
        timestamp deleted_at
    }

    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint menu_id FK
        string nama_menu_snapshot
        decimal price_snapshot
        int qty
        decimal subtotal
        text note
    }

    ORDER_STATUS_HISTORIES {
        bigint id PK
        bigint order_id FK
        bigint changed_by FK "users.id"
        enum status
        text note
        timestamp created_at
    }

    ORDER_ISSUES {
        bigint id PK
        bigint order_id FK
        enum reason "toko_tutup, menu_habis, barang_tidak_ada, cuaca, lainnya"
        text note
        bigint created_by FK "users.id"
        timestamp created_at
    }

    PAYMENTS {
        bigint id PK
        bigint order_id FK
        enum method "cash, transfer, qris"
        enum status "pending, diterima, ditolak"
        decimal amount
        timestamp paid_at
    }

    PAYMENT_PROOFS {
        bigint id PK
        bigint payment_id FK
        string image_path
        timestamp uploaded_at
    }

    ACTIVITY_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        string subject_type
        bigint subject_id
        text description
        string ip_address
        timestamp created_at
    }
```

## Catatan Desain

- **RBAC**: tabel `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` disediakan oleh package `spatie/laravel-permission` (industry-standard, teruji), bukan tabel custom. `User` memakai trait `HasRoles`.
- **Notifications**: memakai tabel bawaan Laravel Notification (`php artisan notifications:table`) — morph table `notifiable_type`/`notifiable_id`, `type`, `data` (JSON), `read_at`.
- **Soft Delete**: diterapkan pada tabel master yang datanya tidak boleh hilang permanen demi integritas histori — `users`, `providers`, `stores`, `menus`, `orders`.
- **Snapshot fields**: `order_items` menyimpan `nama_menu_snapshot` & `price_snapshot`, dan `orders` menyimpan `divisi_snapshot`/`lantai_snapshot` — supaya histori order tetap akurat walau menu/harga/data user berubah di kemudian hari.
- **provider_locations**: schema-only untuk Stage 1. Kolom `latitude`/`longitude`/`located_at` disiapkan agar fitur live GPS tracking (browser geolocation) tinggal diaktifkan di stage mendatang tanpa migrasi ulang.
- **order_status_histories**: dasar data untuk komponen timeline tracking di sisi Pemesan (Menunggu → Diproses → Dibelikan → Diantar → Selesai) dan untuk audit perubahan status oleh Admin/Provider.
- **Foreign keys**: seluruh relasi memakai `foreignId()->constrained()` dengan `onDelete('cascade')` untuk data anak (order_items, payment_proofs, dst) dan `onDelete('restrict')`/`cascade` sesuai konteks agar data histori order tidak hilang saat data referensi dihapus.
