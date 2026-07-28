<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Invoice Pesanan {{ $order->kode_order }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family: Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 0;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; max-width:600px; width:100%;">

    <tr>
        <td style="background-color:#166534; padding:24px 32px;">
            <span style="font-size:22px; font-weight:bold; color:#ffffff;">Wawan<span style="color:#84cc16;">GO</span></span>
            <div style="font-size:11px; letter-spacing:2px; color:#bbf7d0; margin-top:4px;">PESAN &bull; BELI &bull; ANTAR</div>
        </td>
    </tr>

    <tr>
        <td style="padding:28px 32px 8px;">
            <h1 style="margin:0 0 4px; font-size:18px; color:#111827;">Pesanan Anda selesai</h1>
            <p style="margin:0; font-size:14px; color:#6b7280;">
                Terima kasih, {{ $order->user->name }}. Berikut invoice untuk pesanan Anda.
            </p>
        </td>
    </tr>

    <tr>
        <td style="padding:16px 32px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#374151;">
                <tr>
                    <td style="padding:4px 0; color:#6b7280;">Kode Order</td>
                    <td style="padding:4px 0; text-align:right; font-weight:bold;">{{ $order->kode_order }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0; color:#6b7280;">Tanggal Selesai</td>
                    <td style="padding:4px 0; text-align:right;">{{ $order->completed_at?->translatedFormat('d F Y, H:i') }} WIB</td>
                </tr>
                <tr>
                    <td style="padding:4px 0; color:#6b7280;">Toko</td>
                    <td style="padding:4px 0; text-align:right;">{{ $order->store->nama_toko }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0; color:#6b7280;">Penyedia Jasa</td>
                    <td style="padding:4px 0; text-align:right;">{{ $order->provider->user->name }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td style="padding:8px 32px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; border-collapse:collapse;">
                <tr style="background-color:#f9fafb;">
                    <td style="padding:8px 4px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Item</td>
                    <td style="padding:8px 4px; color:#6b7280; border-bottom:1px solid #e5e7eb; text-align:center;">Qty</td>
                    <td style="padding:8px 4px; color:#6b7280; border-bottom:1px solid #e5e7eb; text-align:right;">Harga</td>
                    <td style="padding:8px 4px; color:#6b7280; border-bottom:1px solid #e5e7eb; text-align:right;">Subtotal</td>
                </tr>
                @foreach ($order->items as $item)
                <tr>
                    <td style="padding:8px 4px; border-bottom:1px solid #f3f4f6; color:#111827;">
                        {{ $item->nama_menu_snapshot }}
                        @if ($item->note)
                        <div style="font-size:11px; color:#9ca3af;">{{ $item->note }}</div>
                        @endif
                    </td>
                    <td style="padding:8px 4px; border-bottom:1px solid #f3f4f6; text-align:center; color:#374151;">{{ $item->qty }}</td>
                    <td style="padding:8px 4px; border-bottom:1px solid #f3f4f6; text-align:right; color:#374151;">Rp{{ number_format($item->price_snapshot, 0, ',', '.') }}</td>
                    <td style="padding:8px 4px; border-bottom:1px solid #f3f4f6; text-align:right; color:#111827;">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>

    <tr>
        <td style="padding:8px 32px 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#374151;">
                <tr>
                    <td style="padding:4px 0;">Subtotal</td>
                    <td style="padding:4px 0; text-align:right;">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;">Biaya Layanan</td>
                    <td style="padding:4px 0; text-align:right;">Rp{{ number_format($order->service_fee, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 0 0; font-size:15px; font-weight:bold; color:#111827; border-top:2px solid #e5e7eb;">Total</td>
                    <td style="padding:10px 0 0; font-size:15px; font-weight:bold; color:#166534; text-align:right; border-top:2px solid #e5e7eb;">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td style="background-color:#f9fafb; padding:20px 32px; text-align:center;">
            <p style="margin:0; font-size:12px; color:#9ca3af;">
                Email ini dikirim otomatis oleh WawanGo. Mohon tidak membalas email ini.
            </p>
        </td>
    </tr>

</table>
</td>
</tr>
</table>
</body>
</html>
