<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        h1 {
            font-size: 20px;
            margin: 0;
        }

        .muted {
            color: #666;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
        }

        .info {
            width: 100%;
            margin-bottom: 20px;
        }

        .info td {
            vertical-align: top;
            padding: 2px 0;
        }

        table.list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        table.list th,
        table.list td {
            border: 1px solid #ccc;
            padding: 6px 8px;
        }

        table.list th {
            background: #f0f0f0;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        h3 {
            margin: 16px 0 6px;
            font-size: 13px;
        }

        .total td {
            font-weight: bold;
        }

        .grand {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Bengkel Dinamo</h1>
        <div class="muted">Invoice {{ $invoiceNumber }}</div>
    </div>

    <table class="info">
        <tr>
            <td width="18%">Pelanggan</td>
            <td width="32%">: {{ $service->customer_name }}</td>
            <td width="18%">Tanggal</td>
            <td>: {{ $service->scheduled_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>No. HP</td>
            <td>: {{ $service->phone }}</td>
            <td>Status</td>
            <td>: Selesai</td>
        </tr>
        <tr>
            <td>Keluhan</td>
            <td colspan="3">: {{ $service->complaint }}</td>
        </tr>
    </table>

    <h3>Jasa</h3>
    <table class="list">
        <thead>
            <tr>
                <th>Jasa</th>
                <th class="right" width="25%">Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($service->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td class="right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="center muted">Tidak ada jasa</td>
                </tr>
            @endforelse
            <tr class="total">
                <td class="right">Total Jasa</td>
                <td class="right">Rp {{ number_format($service->labor_total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Spare Part</h3>
    <table class="list">
        <thead>
            <tr>
                <th>Part</th>
                <th class="center" width="10%">Qty</th>
                <th class="right" width="20%">Harga</th>
                <th class="right" width="22%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($service->serviceParts as $part)
                <tr>
                    <td>{{ $part->sparePart->name }}</td>
                    <td class="center">{{ $part->quantity }}</td>
                    <td class="right">Rp {{ number_format($part->unit_price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($part->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center muted">Tidak ada spare part</td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="3" class="right">Total Spare Part</td>
                <td class="right">Rp {{ number_format($service->parts_total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if ((float) $service->transport_fee > 0)
        <h3>Transport</h3>
        <table class="list">
            <tbody>
                <tr>
                    <td>
                        Transport (BBM)
                        @if ($service->distance_km)
                            <span class="muted">· jarak {{ number_format($service->distance_km, 1, ',', '.') }} km,
                                pulang-pergi</span>
                        @endif
                    </td>
                    <td class="right" width="25%">Rp {{ number_format($service->transport_fee, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <table class="list">
        <tr class="total grand">
            <td class="right">TOTAL BAYAR</td>
            <td class="right" width="25%">Rp {{ number_format($service->grand_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p class="muted center">Terima kasih telah mempercayakan dinamo Anda kepada kami.</p>
</body>

</html>