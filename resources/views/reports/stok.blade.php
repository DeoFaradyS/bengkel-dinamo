<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Stok</title>
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
        .low { color: #b91c1c; font-weight: bold; }
        h3 { margin: 14px 0 6px; font-size: 12px; }
        .cards td { border: 1px solid #ccc; padding: 8px; width: 25%; vertical-align: top; }
        .cards .big { font-size: 14px; font-weight: bold; margin-top: 2px; }
    </style>
</head>

<body>
    @php
        $logo = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo-dinamo.png')));
        $tanda = fn (int $n) => $n > 0 ? '+' . $n : (string) $n;
    @endphp

    <table class="header">
        <tr>
            <td style="vertical-align: middle;">
                <img src="{{ $logo }}" alt="Dinamo, Bengkel Cak Budi" style="height: 40px;">
                <div class="muted" style="margin-top: 6px;">Nguling, Pasuruan, Jawa Timur &middot; +62 888-2509-745</div>
            </td>
            <td class="right" style="vertical-align: middle;">
                <h1>LAPORAN STOK</h1>
                <div class="muted">Periode {{ $period }}</div>
            </td>
        </tr>
    </table>

    <p class="muted" style="margin: 0 0 10px;">Mutasi persediaan suku cadang. Satuan mengikuti nama barang (contoh set, kg), selain itu per biji. Stok akhir = stok awal + masuk - keluar + penyesuaian.</p>

    <table class="cards" style="width: 100%; border-collapse: collapse; margin-bottom: 14px;">
        <tr>
            <td><div class="muted">Barang masuk</div><div class="big">{{ $totals['in'] }}</div></td>
            <td><div class="muted">Barang keluar</div><div class="big">{{ $totals['out'] }}</div></td>
            <td><div class="muted">Penyesuaian</div><div class="big">{{ $tanda($totals['adjustment']) }}</div></td>
            <td><div class="muted">Hampir habis</div><div class="big">{{ $totals['low'] }}</div></td>
        </tr>
    </table>

    <h3>Mutasi persediaan</h3>
    <table class="list">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Kode</th>
                <th width="30%">Nama barang</th>
                <th class="right">Stok awal</th>
                <th class="right">Masuk</th>
                <th class="right">Keluar</th>
                <th class="right">Penyesuaian</th>
                <th class="right">Stok akhir</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $r['code'] }}</td>
                    <td>{{ $r['name'] }}{{ $r['archived'] ? ' (diarsip)' : '' }}</td>
                    <td class="right">{{ $r['opening'] }}</td>
                    <td class="right">{{ $r['in'] }}</td>
                    <td class="right">{{ $r['out'] }}</td>
                    <td class="right">{{ $tanda($r['adjustment']) }}</td>
                    <td class="right"><strong>{{ $r['closing'] }}</strong></td>
                    <td>@if ($r['low'])<span class="low">Hampir habis</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted" style="text-align: center;">Belum ada suku cadang</td></tr>
            @endforelse
            <tr class="total">
                <td colspan="4">Jumlah</td>
                <td class="right">{{ $totals['in'] }}</td>
                <td class="right">{{ $totals['out'] }}</td>
                <td class="right">{{ $tanda($totals['adjustment']) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <p class="muted">Nilai persediaan (harga beli) tidak dihitung karena harga beli suku cadang belum dicatat. Dicetak {{ now()->translatedFormat('j F Y, H.i') }}.</p>
</body>

</html>
