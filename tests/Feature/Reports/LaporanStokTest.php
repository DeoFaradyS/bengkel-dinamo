<?php

use App\Enums\StockMovementType;
use App\Enums\StockReason;
use App\Filament\Pages\LaporanStok;
use App\Filament\Widgets\Laporan\LaporanStokKpi;
use App\Models\SparePart;
use App\Models\User;
use Livewire\Livewire;

// Laporan Stok / mutasi persediaan (21 Nov 2026, aturan 31). Kode modul: LSK.

beforeEach(fn () => $this->actingAs(User::factory()->owner()->create()));

// Part dengan stok awal dibuat pada tanggal tertentu (riwayat Opening ikut tanggal itu).
function lskPart(string $tanggal, int $stok, array $o = []): SparePart
{
    test()->travelTo($tanggal);

    return SparePart::factory()->create(array_merge(['stock' => $stok, 'min_stock' => 2], $o));
}

function lskGerak(SparePart $part, string $tanggal, int $jumlah, StockMovementType $type, ?StockReason $reason = null): void
{
    test()->travelTo($tanggal);
    $part->addStock($jumlah, $type, null, null, $reason);
}

function lskBaris(string $range, SparePart $part): ?array
{
    $rows = Livewire::test(LaporanStok::class)->set('filters.range', $range)->instance()->mutation()->rows();

    return collect($rows)->firstWhere('id', $part->id);
}

test('LSK-001: halaman Laporan Stok terbuka untuk owner', function () {
    $this->get('/admin/laporan-stok')->assertOk()->assertSee('Mutasi persediaan');
});

test('LSK-002: stok awal dari riwayat sebelum periode, masuk/keluar/penyesuaian hanya di periode', function () {
    $part = lskPart('2026-10-05 09:00:00', 20);                                        // sebelum periode
    lskGerak($part, '2026-10-20 09:00:00', -3, StockMovementType::Usage);              // sebelum periode
    lskGerak($part, '2026-11-03 09:00:00', 10, StockMovementType::Purchase, StockReason::Supplier);
    lskGerak($part, '2026-11-05 09:00:00', -4, StockMovementType::Usage);
    lskGerak($part, '2026-11-06 09:00:00', -2, StockMovementType::Usage);
    lskGerak($part, '2026-11-07 09:00:00', -1, StockMovementType::Correction, StockReason::Lost);
    lskGerak($part, '2026-11-25 09:00:00', 50, StockMovementType::Purchase, StockReason::Supplier); // sesudah periode
    $this->travelTo('2026-11-30 12:00:00');

    expect(lskBaris('01/11/2026 - 20/11/2026', $part))->toMatchArray([
        'opening' => 17, 'in' => 10, 'out' => 6, 'adjustment' => -1, 'closing' => 20,
    ]);
});

test('LSK-003: part baru di tengah periode, stok awalnya 0 dan stok awal part masuk ke kolom Masuk', function () {
    $part = lskPart('2026-11-10 09:00:00', 8);
    $this->travelTo('2026-11-20 12:00:00');

    expect(lskBaris('01/11/2026 - 20/11/2026', $part))->toMatchArray(['opening' => 0, 'in' => 8, 'out' => 0, 'closing' => 8]);
});

test('LSK-004: periode sampai hari ini, stok akhir sama dengan stok sebenarnya di menu Spare Part', function () {
    $part = lskPart('2026-09-01 09:00:00', 30);
    lskGerak($part, '2026-11-02 09:00:00', -7, StockMovementType::Usage);
    lskGerak($part, '2026-11-04 09:00:00', 2, StockMovementType::Correction, StockReason::Miscount);
    $this->travelTo('2026-11-20 12:00:00');

    expect(lskBaris('01/11/2026 - 20/11/2026', $part)['closing'])->toBe($part->fresh()->stock)->toBe(25);
});

test('LSK-005: hampir habis kalau stok akhir <= stok minimal, batas tepat sama ikut', function () {
    $pas = lskPart('2026-11-01 09:00:00', 3, ['min_stock' => 3]);
    $aman = lskPart('2026-11-01 09:00:00', 4, ['min_stock' => 3]);
    $this->travelTo('2026-11-20 12:00:00');

    expect(lskBaris('01/11/2026 - 20/11/2026', $pas)['low'])->toBeTrue()
        ->and(lskBaris('01/11/2026 - 20/11/2026', $aman)['low'])->toBeFalse();
});

test('LSK-006: part diarsip hanya tampil kalau ada pergerakan di periode, kodenya tanpa akhiran -DEL', function () {
    $diam = lskPart('2026-09-01 09:00:00', 5, ['code' => 'SP-90']);
    $gerak = lskPart('2026-09-01 09:00:00', 5, ['code' => 'SP-91']);
    lskGerak($gerak, '2026-11-05 09:00:00', -2, StockMovementType::Usage);
    $diam->delete();
    $gerak->delete();
    $this->travelTo('2026-11-20 12:00:00');

    $baris = lskBaris('01/11/2026 - 20/11/2026', $gerak);

    expect(lskBaris('01/11/2026 - 20/11/2026', $diam))->toBeNull()
        ->and($baris)->toMatchArray(['code' => 'SP-91', 'archived' => true, 'out' => 2, 'low' => false]);
});

test('LSK-007: kartu ringkasan menjumlahkan masuk, keluar, penyesuaian, dan hampir habis', function () {
    $a = lskPart('2026-09-01 09:00:00', 10);
    $b = lskPart('2026-11-02 09:00:00', 1, ['min_stock' => 2]);              // masuk 1, hampir habis
    lskGerak($a, '2026-11-03 09:00:00', 5, StockMovementType::Purchase, StockReason::Supplier);
    lskGerak($a, '2026-11-04 09:00:00', -3, StockMovementType::Usage);
    lskGerak($a, '2026-11-05 09:00:00', -1, StockMovementType::Correction, StockReason::Damaged);
    $this->travelTo('2026-11-20 12:00:00');

    $kartu = collect(LaporanStokKpi::cards(['range' => '01/11/2026 - 20/11/2026']))->pluck('value', 'label')->all();

    expect($kartu)->toBe([
        'Barang masuk' => '6',
        'Barang keluar' => '3',
        'Penyesuaian' => '-1',
        'Hampir habis' => '1',
    ]);
});

test('LSK-008: tombol Unduh PDF menghasilkan file PDF', function () {
    lskPart('2026-11-02 09:00:00', 5);
    $this->travelTo('2026-11-20 12:00:00');

    $page = Livewire::test(LaporanStok::class)->set('filters.range', '01/11/2026 - 20/11/2026');

    expect(substr($page->instance()->pdf(), 0, 4))->toBe('%PDF');

    $page->callAction('unduh_pdf')->assertFileDownloaded();
});
