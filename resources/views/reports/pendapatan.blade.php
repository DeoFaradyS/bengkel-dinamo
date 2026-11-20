<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 18px; margin: 0; }
        .muted { color: #666; }
        .header { width: 100%; margin-bottom: 16px; border-bottom: 2px solid #222; padding-bottom: 10px; }
        .right { text-align: right; }
        table.list { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.list th, table.list td { border: 1px solid #ccc; padding: 5px 6px; }
        table.list th { background: #f0f0f0; text-align: left; }
        .total td { font-weight: bold; background: #f7f7f7; }
        h3 { margin: 14px 0 6px; font-size: 12px; }
        .cards td { border: 1px solid #ccc; padding: 8px; width: 25%; vertical-align: top; }
        .cards .big { font-size: 14px; font-weight: bold; margin-top: 2px; }
    </style>
</head>

<body>
    @php
        $rupiah = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $logo = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo-dinamo.png')));
        $avg = $summary['count'] > 0 ? $summary['total'] / $summary['count'] : 0;
        $persen = fn ($n) => $summary['total'] > 0 ? round($n / $summary['total'] * 100) . '%' : '0%';
    @endphp

    <table class="header">
        <tr>
            <td style="vertical-align: middle;">
                <img src="{{ $logo }}" alt="Dinamo, Bengkel Cak Budi" style="height: 40px;">
                <div class="muted" style="margin-top: 6px;">Nguling, Pasuruan, Jawa Timur &middot; +62 888-2509-745</div>
            </td>
            <td class="right" style="vertical-align: middle;">
                <h1>LAPORAN PENDAPATAN</h1>
                <div class="muted">Periode {{ $period }}</div>
                <div class="muted">Tipe layanan: {{ $type }}</div>
            </td>
        </tr>
    </table>

    <p class="muted" style="margin: 0 0 10px;">Pendapatan dihitung dari servis yang sudah lunas, menurut tanggal lunas.</p>

    <table class="cards" style="width: 100%; border-collapse: collapse; margin-bottom: 6px;">
        <tr>
            <td><div class="muted">Total pendapatan</div><div class="big">{{ $rupiah($summary['total']) }}</div></td>
            <td><div class="muted">Servis lunas</div><div class="big">{{ $summary['count'] }}</div></td>
            <td><div class="muted">Rata-rata per servis</div><div class="big">{{ $rupiah($avg) }}</div></td>
            <td><div class="muted">Belum dibayar</div><div class="big">{{ $rupiah($unpaid['total']) }}</div><div class="muted">{{ $unpaid['count'] }} servis, semua tanggal</div></td>
        </tr>
    </table>

    <h3>Rincian sumber pendapatan</h3>
    <table class="list">
        <thead>
            <tr><th>Sumber</th><th class="right" width="25%">Jumlah</th><th class="right" width="12%">Persen</th></tr>
        </thead>
        <tbody>
            @foreach ([['Jasa', $summary['jobs']], ['Spare part', $summary['parts']], ['Transport (Panggil Montir)', $summary['transport']]] as [$label, $nilai])
                <tr><td>{{ $label }}</td><td class="right">{{ $rupiah($nilai) }}</td><td class="right">{{ $persen($nilai) }}</td></tr>
            @endforeach
            <tr class="total"><td>Total</td><td class="right">{{ $rupiah($summary['total']) }}</td><td class="right">{{ $summary['total'] > 0 ? '100%' : '0%' }}</td></tr>
        </tbody>
    </table>

    <h3>Daftar servis lunas</h3>
    <table class="list">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Tanggal lunas</th>
                <th>Kode</th>
                <th>Pelanggan</th>
                <th>Tipe</th>
                <th class="right">Jasa</th>
                <th class="right">Spare part</th>
                <th class="right">Transport</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                @php $total = (float) ($r->items_sum_price ?? 0) + (float) ($r->parts_sum ?? 0) + (float) ($r->transport_fee ?? 0); @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $r->paid_at->translatedFormat('j M Y, H.i') }}</td>
                    <td>{{ $r->booking_code }}</td>
                    <td>{{ $r->customer_name }}</td>
                    <td>{{ $r->booking_type->getLabel() }}</td>
                    <td class="right">{{ $rupiah($r->items_sum_price ?? 0) }}</td>
                    <td class="right">{{ $rupiah($r->parts_sum ?? 0) }}</td>
                    <td class="right">{{ $rupiah($r->transport_fee ?? 0) }}</td>
                    <td class="right">{{ $rupiah($total) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted" style="text-align: center;">Belum ada pendapatan di periode ini</td></tr>
            @endforelse
            <tr class="total">
                <td colspan="5">Jumlah</td>
                <td class="right">{{ $rupiah($summary['jobs']) }}</td>
                <td class="right">{{ $rupiah($summary['parts']) }}</td>
                <td class="right">{{ $rupiah($summary['transport']) }}</td>
                <td class="right">{{ $rupiah($summary['total']) }}</td>
            </tr>
        </tbody>
    </table>

    <p class="muted">Dicetak {{ now()->translatedFormat('j F Y, H.i') }}.</p>
</body>

</html>
