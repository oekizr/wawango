# WawanGo — Struktur Folder & Konvensi

## Struktur Backend (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # controller khusus role admin (Stage 2)
│   │   ├── Provider/         # controller khusus role penyedia jasa (Stage 3)
│   │   ├── Pemesan/          # controller khusus role pemesan (Stage 4)
│   │   ├── Auth/             # bawaan Breeze
│   │   └── ProfileController.php
│   ├── Middleware/
│   │   └── EnsureUserHasRole.php   # (bila diperlukan di luar spatie middleware)
│   ├── Requests/
│   │   ├── Admin/
│   │   ├── Provider/
│   │   └── Pemesan/
│   └── Resources/            # API Resource untuk transformasi data ke Inertia/JSON
├── Models/
│   ├── User.php
│   ├── Provider.php
│   ├── ProviderSchedule.php
│   ├── ProviderLocation.php
│   ├── Store.php
│   ├── MenuCategory.php
│   ├── Menu.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── OrderStatusHistory.php
│   ├── OrderIssue.php
│   ├── Payment.php
│   ├── PaymentProof.php
│   └── ActivityLog.php
├── Repositories/              # dibuat mulai Stage 2 saat CRUD nyata pertama dibangun
├── Services/                  # dibuat mulai Stage 2 (mis. OrderService, PaymentService)
├── Observers/                 # mis. ActivityLogObserver (Stage 2+)
├── Events/                    # mis. OrderCreated, OrderStatusUpdated (Stage 6)
├── Listeners/
├── Notifications/             # Laravel Notification classes (Stage 6)
└── Policies/                  # otorisasi per model (Stage 2+)

database/
├── migrations/
├── seeders/
└── factories/

docs/                          # dokumen desain (tahap ini)
routes/
├── web.php                    # route umum + auth
├── admin.php                  # route group role admin (Stage 2, di-require dari web.php)
├── provider.php                # route group role provider (Stage 3)
└── pemesan.php                 # route group role pemesan (Stage 4)
```

**Konvensi Repository/Service**: setiap modul bisnis (Stage 2+) memakai pola `Controller → Service → Repository → Model`. Controller tetap tipis (validasi via Form Request, delegasi ke Service), Service berisi business logic (mis. kalkulasi total, perubahan status order + broadcast event), Repository membungkus query Eloquent yang kompleks/reusable. Untuk operasi CRUD sederhana tanpa logic tambahan, Controller boleh memanggil Model langsung tanpa Repository — pola ini tidak dipaksakan di semua tempat agar tidak menjadi abstraksi berlebihan.

## Struktur Frontend (Inertia + Vue 3)

```
resources/js/
├── Components/
│   ├── UI/                    # Button, Card, Badge, Modal, Skeleton, Toast (shared, dipakai semua role)
│   └── ...
├── Layouts/
│   ├── AuthenticatedLayout.vue  # bawaan Breeze, jadi basis
│   ├── GuestLayout.vue
│   ├── AdminLayout.vue          # sidebar admin
│   ├── ProviderLayout.vue       # navigasi provider
│   └── PemesanLayout.vue        # navigasi pemesan (bottom nav mobile)
├── Pages/
│   ├── Auth/                    # bawaan Breeze
│   ├── Admin/
│   │   └── Dashboard.vue        # placeholder Stage 1, diisi Stage 2
│   ├── Provider/
│   │   └── Dashboard.vue        # placeholder Stage 1, diisi Stage 3
│   ├── Pemesan/
│   │   └── Dashboard.vue        # placeholder Stage 1, diisi Stage 4
│   └── Profile/
├── stores/                      # Pinia
│   └── auth.js                  # state user & role aktif
├── composables/                 # useXxx() reusable logic (Stage 2+)
└── app.js
```

**Penamaan Vue Pages** mengikuti role: `resources/js/Pages/{Role}/{Fitur}.vue`, sesuai path Inertia yang dikembalikan controller masing-masing (`Admin/Dashboard`, `Provider/Dashboard`, `Pemesan/Dashboard`).

## Alasan Pemilihan Pola

- **Repository + Service** dipakai bertahap sesuai kebutuhan nyata (bukan dibuat kosong di Stage 1) — mengikuti prinsip tidak membangun abstraksi untuk fitur yang belum ada.
- **Route per role** dipisah file agar `web.php` tetap ringkas dan RBAC route grouping eksplisit terlihat di level file.
- **Layout per role** memisahkan navigasi (sidebar admin vs bottom-nav mobile pemesan) tanpa mencampur kondisional besar dalam satu komponen.
