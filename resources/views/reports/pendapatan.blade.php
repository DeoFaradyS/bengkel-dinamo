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
        .right { text-align: right; white-space: nowrap; }
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
            </td>
        </tr>
    </table>

    <p class="muted" style="margin: 0 0 10px;">Pendapatan dihitung dari servis yang sudah lunas, menurut tanggal lunas.</p>

    <table class="cards" style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
        <tr>
            <td style="width: 50%;"><div class="muted">Uang masuk</div><div class="big">{{ $rupiah($summary['total']) }}</div>
                <div class="muted">Jasa {{ $rupiah($summary['jobs']) }} &middot; Part {{ $rupiah($summary['parts']) }} &middot; Transport {{ $rupiah($summary['transport']) }}</div></td>
            <td><div class="muted">Servis lunas</div><div class="big">{{ $summary['count'] }}</div></td>
            <td><div class="muted">Rata-rata per servis</div><div class="big">{{ $rupiah($avg) }}</div></td>
        </tr>
    </table>
    <p class="muted" style="margin: 0 0 10px; font-size: 10px;">Uang masuk belum dikurangi modal spare part, jadi bukan keuntungan bersih.</p>

    <h3>Rekap per {{ strtolower($recapUnit) }}</h3>
    <table class="list">
        <thead>
            <tr>
                <th>{{ $recapUnit === 'Hari' ? 'Tanggal' : $recapUnit }}</th>
                <th class="right">Servis</th>
                <th class="right">Jasa</th>
                <th class="right">Spare part</th>
                <th class="right">Transport</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recap as $r)
                <tr>
                    <td>{{ $r['label'] }}</td>
                    <td class="right">{{ $r['count'] }}</td>
                    <td class="right">{{ $rupiah($r['jobs']) }}</td>
                    <td class="right">{{ $rupiah($r['parts']) }}</td>
                    <td class="right">{{ $rupiah($r['transport']) }}</td>
                    <td class="right">{{ $rupiah($r['total']) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted" style="text-align: center;">Belum ada uang masuk di periode ini</td></tr>
            @endforelse
            <tr class="total">
                <td>Jumlah</td>
                <td class="right">{{ $summary['count'] }}</td>
                <td class="right">{{ $rupiah($summary['jobs']) }}</td>
                <td class="right">{{ $rupiah($summary['parts']) }}</td>
                <td class="right">{{ $rupiah($summary['transport']) }}</td>
                <td class="right">{{ $rupiah($summary['total']) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Rincian transaksi</h3>
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

    <h3>Piutang (servis selesai, belum dibayar), semua tanggal</h3>
    <table class="list">
        <thead>
            <tr><th width="5%">No</th><th>Tanggal servis</th><th>Kode</th><th>Pelanggan</th><th class="right">Total</th></tr>
        </thead>
        <tbody>
            @forelse ($unpaidRows as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $p->scheduled_at?->translatedFormat('j M Y') }}</td>
                    <td>{{ $p->booking_code }}</td>
                    <td>{{ $p->customer_name }}</td>
                    <td class="right">{{ $rupiah($p->grand_total) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted" style="text-align: center;">Tidak ada piutang</td></tr>
            @endforelse
            <tr class="total"><td colspan="4">Jumlah</td><td class="right">{{ $rupiah($unpaid['total']) }}</td></tr>
        </tbody>
    </table>

    <p class="muted">Dicetak {{ now()->translatedFormat('j F Y, H.i') }}.</p>
</body>

</html>
