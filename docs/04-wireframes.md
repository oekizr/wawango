# WawanGo — Wireframe UI (Low-Fidelity)

Wireframe struktural (bukan visual final) untuk memandu penyusunan komponen Vue di stage-stage berikutnya. Semua layar mobile-first (lebar dasar ~375px), dengan breakpoint lebar untuk desktop admin.

## 1. Dashboard Admin (desktop-first, sidebar)

```
┌─────────────────────────────────────────────────────────┐
│ [Logo WawanGo]      Dashboard                 [🔔] [👤▾] │
├───────────────┬─────────────────────────────────────────┤
│ Sidebar        │  ┌───────┐ ┌───────┐ ┌───────┐ ┌───────┐│
│ ▸ Dashboard    │  │Total  │ │Total  │ │Order  │ │Order  ││
│ ▸ Provider     │  │Provdr │ │User   │ │Hari Ini│ │Berjalan││
│ ▸ User         │  └───────┘ └───────┘ └───────┘ └───────┘│
│ ▸ Order        │  ┌───────────────────┐ ┌───────────────┐│
│ ▸ Laporan      │  │ Grafik Transaksi  │ │ Order Selesai ││
│ ▸ Pengaturan   │  │ (line/bar chart)  │ │ vs Dibatalkan ││
│                │  └───────────────────┘ └───────────────┘│
└───────────────┴─────────────────────────────────────────┘
```

## 2. Dashboard Penyedia Jasa (mobile-first)

```
┌───────────────────────────┐
│ WawanGo Partner    [🔔3]  │
├───────────────────────────┤
│ Status Layanan             │
│  ┌─────────────────────┐  │
│  │  🟢 BUKA   [Tutup]   │  │
│  └─────────────────────┘  │
│  Jadwal: Sen-Jum 08.00-09 │
├───────────────────────────┤
│ ┌───────────┐ ┌──────────┐│
│ │Order Aktif│ │Pendapatan││
│ │    5      │ │Hari Ini  ││
│ │           │ │Rp250.000 ││
│ └───────────┘ └──────────┘│
├───────────────────────────┤
│ Order Masuk                │
│ ┌─────────────────────┐   │
│ │ #WG0012 - Budi       │  │
│ │ Divisi IT · Lt.3      │  │
│ │ 2 item · Rp45.000     │  │
│ │ [Proses] [Kendala]    │  │
│ └─────────────────────┘   │
│ [Lihat semua order →]     │
└───────────────────────────┘
```

## 3. Dashboard Pemesan (mobile-first)

```
┌───────────────────────────┐
│ Halo, Budi 👋       [🔔]  │
│ [🔍 Cari penyedia jasa...] │
├───────────────────────────┤
│ Order Aktif                │
│ ┌─────────────────────┐   │
│ │ #WG0012 · Diproses    │  │
│ │ ●──●──○──○──○         │  │
│ │ [Lihat detail →]      │  │
│ └─────────────────────┘   │
├───────────────────────────┤
│ Penyedia Jasa Tersedia      │
│ ┌───────────┐┌───────────┐│
│ │🟢 Wawan   ││⚪ Sari     ││
│ │08.00-09.00││Tutup       ││
│ │2 toko     ││(disabled)  ││
│ └───────────┘└───────────┘│
└───────────────────────────┘
```

## 4. Pilih Toko & Menu

```
┌───────────────────────────┐
│ ← Toko milik Wawan          │
├───────────────────────────┤
│ ┌─────────────────────┐   │
│ │ Warteg Bahari         │  │
│ │ Biaya jasa Rp10.000    │  │
│ └─────────────────────┘   │
│ ┌─────────────────────┐   │
│ │ [foto] Nasi Uduk       │  │
│ │ Rp15.000    [- 1 +]    │  │
│ │ [+ Catatan: tdk pedas] │  │
│ └─────────────────────┘   │
│ ┌─────────────────────┐   │
│ │ [foto] Es Teh          │  │
│ │ Rp5.000  (Habis)       │  │
│ └─────────────────────┘   │
│ [Keranjang (2) →]          │
└───────────────────────────┘
```

## 5. Checkout

```
┌───────────────────────────┐
│ ← Checkout                  │
├───────────────────────────┤
│ Nasi Uduk x1        15.000 │
│ Es Teh x1 (habis, dihapus) │
│ ─────────────────────────  │
│ Subtotal            15.000 │
│ Biaya Jasa           10.000 │
│ Grand Total          25.000 │
├───────────────────────────┤
│ Metode Pembayaran            │
│ ( ) Cash                    │
│ (•) Transfer Bank            │
│     BCA 123456 a.n Wawan     │
│     [Upload bukti transfer]  │
│ ( ) QRIS                    │
├───────────────────────────┤
│ [   Buat Pesanan   ]        │
└───────────────────────────┘
```

## 6. Tracking Order

```
┌───────────────────────────┐
│ ← Order #WG0012             │
├───────────────────────────┤
│  ● Menunggu     08:01       │
│  │                          │
│  ● Diproses     08:03       │
│  │                          │
│  ○ Dibelikan                │
│  │                          │
│  ○ Diantar                  │
│  │                          │
│  ○ Selesai                  │
├───────────────────────────┤
│ Detail Pesanan               │
│ Nasi Uduk x1 · Rp15.000      │
│ Total: Rp25.000              │
│ Pembayaran: Transfer (pending)│
└───────────────────────────┘
```

## Prinsip UI/UX

- Warna utama hijau `#22C55E`, putih, abu muda; dark mode via Tailwind `class` strategy.
- Card modern rounded + shadow halus, ikon Heroicons, loading skeleton saat fetch data, toast notification untuk aksi (submit order, upload bukti, dsb).
- Mobile-first untuk Pemesan & Provider; layout sidebar desktop-first untuk Admin (tetap responsif ke mobile).
