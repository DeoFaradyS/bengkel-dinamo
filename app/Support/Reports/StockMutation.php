<?php

namespace App\Support\Reports;

use App\Enums\StockMovementType;
use App\Models\SparePart;
use App\Models\StockMovement;
use App\Support\Qty;
use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Laporan mutasi persediaan (aturan 31), dihitung dari riwayat stok (stock_movements).
 *
 * - Stok awal  = jumlah semua perubahan sebelum periode.
 * - Masuk      = stok awal part baru (Opening) + pembelian/retur (Purchase) di periode.
 * - Keluar     = dipakai servis (Usage) di periode, ditulis positif.
 * - Penyesuaian = koreksi (rusak, hilang, salah hitung, lainnya) di periode, bertanda + atau -.
 * - Stok akhir = stok awal + masuk - keluar + penyesuaian.
 *
 * Tanpa penanda hampir habis: bukan bagian laporan mutasi standar, pengingatnya ada di menu
 * Suku Cadang (baris merah). Semua part aktif tampil, part yang diarsip hanya tampil kalau ada pergerakan di periode.
 * Setiap baris memakai satuan barangnya (aturan 33). Jumlah barang tidak dijumlahkan antar barang
 * karena satuannya beda (2 kg + 2 pcs tidak bermakna); ringkasan menghitung jenis barang.
 * Nilai persediaan tidak dihitung karena harga beli tidak dicatat.
 */
final class StockMutation
{
    private Carbon $from;

    private Carbon $to;

    public function __construct(DateTimeInterface $from, DateTimeInterface $to)
    {
        $this->from = Carbon::instance($from)->startOfDay();
        $this->to = Carbon::instance($to)->endOfDay();
    }

    /**
     * @return list<array{id: int, code: string, name: string, unit: string, archived: bool, opening: int|float, in: int|float, out: int|float, adjustment: int|float, closing: int|float}>
     */
    public function rows(): array
    {
        $in = [StockMovementType::Opening->value, StockMovementType::Purchase->value];

        $sums = StockMovement::query()
            ->where('created_at', '<=', $this->to)
            ->groupBy('spare_part_id')
            ->selectRaw('spare_part_id')
            ->selectRaw('SUM(CASE WHEN created_at < ? THEN quantity_change ELSE 0 END) as opening', [$this->from])
            ->selectRaw('SUM(CASE WHEN created_at >= ? AND type IN (?, ?) THEN quantity_change ELSE 0 END) as qty_in', [$this->from, ...$in])
            ->selectRaw('SUM(CASE WHEN created_at >= ? AND type = ? THEN quantity_change ELSE 0 END) as qty_out', [$this->from, StockMovementType::Usage->value])
            ->selectRaw('SUM(CASE WHEN created_at >= ? AND type = ? THEN quantity_change ELSE 0 END) as qty_adj', [$this->from, StockMovementType::Correction->value])
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as moves', [$this->from])
            ->get()
            ->keyBy('spare_part_id');

        return SparePart::withTrashed()
            ->orderBy('code')
            ->get()
            ->filter(fn(SparePart $p) => ! $p->trashed() || (int) ($sums[$p->id]->moves ?? 0) > 0)
            ->map(function (SparePart $p) use ($sums) {
                $s = $sums[$p->id] ?? null;
                $opening = Qty::number($s->opening ?? 0);
                $in = Qty::number($s->qty_in ?? 0);
                $out = Qty::number(-($s->qty_out ?? 0));
                $adjustment = Qty::number($s->qty_adj ?? 0);
                $closing = Qty::number($opening + $in - $out + $adjustment);

                return [
                    'id' => $p->id,
                    // Kode part yang diarsip diberi akhiran -DEL-id (lihat SparePart), dibuang untuk laporan.
                    'code' => preg_replace('/-DEL-\d+$/', '', $p->code),
                    'name' => $p->name,
                    'unit' => $p->unit->label(),
                    'archived' => $p->trashed(),
                    'opening' => $opening,
                    'in' => $in,
                    'out' => $out,
                    'adjustment' => $adjustment,
                    'closing' => $closing,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Ringkasan dalam jumlah JENIS barang, bukan jumlah barang (beda satuan tidak boleh dijumlah).
     *
     * @return array{in: int, out: int, adjustment: int, total: int}
     */
    public function totals(): array
    {
        $rows = collect($this->rows());

        return [
            'in' => $rows->where('in', '>', 0)->count(),
            'out' => $rows->where('out', '>', 0)->count(),
            'adjustment' => $rows->where('adjustment', '!=', 0)->count(),
            'total' => $rows->count(),
        ];
    }
}
