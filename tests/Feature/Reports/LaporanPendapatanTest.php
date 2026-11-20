<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Filament\Pages\LaporanPendapatan;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use Livewire\Livewire;

// Laporan Pendapatan (20 Nov 2026, aturan 23). Kode modul: LAP.
// Dasar uang diterima: servis Selesai yang lunas, menurut tanggal lunas.

beforeEach(fn () => $this->actingAs(User::factory()->owner()->create()));

function lapServis(string $paidAt, float $jasa, array $o = []): Service
{
    $s = Service::factory()->create(array_merge([
        'status' => ServiceStatus::Completed,
        'scheduled_at' => $paidAt,
        'paid_at' => $paidAt,
        'booking_type' => BookingType::Workshop,
    ], $o));

    $s->items()->create(['service_type_id' => ServiceType::factory()->create()->id, 'name' => 'Servis dinamo', 'price' => $jasa]);

    return $s;
}

function lapBuka(string $range)
{
    return Livewire::test(LaporanPendapatan::class)->set('filters.range', $range);
}

function lapRentang($dari, $sampai): string
{
    return $dari->format('d/m/Y') . ' - ' . $sampai->format('d/m/Y');
}

test('LAP-001: halaman Laporan Pendapatan terbuka untuk owner', function () {
    $this->get('/admin/laporan-pendapatan')
        ->assertOk()
        ->assertSee('Tanggal bayar');
});

test('LAP-002: tabel hanya berisi servis lunas di periode, yang belum lunas atau di luar periode tidak', function () {
    $lunas = lapServis(now()->subDays(2)->setTime(10, 0)->toDateTimeString(), 100000);
    $belum = lapServis(now()->subDays(2)->setTime(11, 0)->toDateTimeString(), 200000, ['paid_at' => null]);
    $lama = lapServis(now()->subDays(40)->setTime(10, 0)->toDateTimeString(), 300000);

    lapBuka(lapRentang(now()->subDays(7), now()))
        ->assertCanSeeTableRecords([$lunas])
        ->assertCanNotSeeTableRecords([$belum, $lama]);
});

// LAP-003 (filter tipe layanan) dihapus 20 Nov 2026 karena filter disamakan dengan dasbor, jangan dipakai ulang.

test('LAP-004: ringkasan = jasa + spare part + transport, belum dibayar terpisah', function () {
    $s = lapServis(now()->subDay()->setTime(10, 0)->toDateTimeString(), 100000);
    $s->forceFill(['transport_fee' => 20000])->saveQuietly();
    lapServis(now()->subDay()->setTime(11, 0)->toDateTimeString(), 50000, ['paid_at' => null]);

    $page = lapBuka(lapRentang(now()->subDays(7), now()))->instance();

    expect($page->summary())->toBe(['jobs' => 100000.0, 'parts' => 0.0, 'transport' => 20000.0, 'total' => 120000.0, 'count' => 1])
        ->and($page->unpaid()['total'])->toEqualWithDelta(50000, 0.01);
});

test('LAP-005: tombol Unduh PDF menghasilkan file PDF', function () {
    lapServis(now()->subDay()->setTime(10, 0)->toDateTimeString(), 100000);

    $page = lapBuka(lapRentang(now()->subDays(7), now()));

    expect(substr($page->instance()->pdf(), 0, 4))->toBe('%PDF');

    $page->callAction('unduh_pdf')->assertFileDownloaded();
});

// Rekap per hari / minggu / bulan dan piutang (aturan 30).

test('LAP-006: rekap per hari menjumlahkan per tanggal lunas, hari tanpa pemasukan tidak tampil', function () {
    $this->travelTo('2026-11-20 12:00:00');
    $a = lapServis('2026-11-03 09:00:00', 100000);
    $a->forceFill(['transport_fee' => 20000])->saveQuietly();
    lapServis('2026-11-03 15:00:00', 50000);
    lapServis('2026-11-05 10:00:00', 70000);

    $rekap = lapBuka('01/11/2026 - 20/11/2026')->instance()->recap();

    expect($rekap)->toHaveCount(2)
        ->and($rekap[0])->toMatchArray(['count' => 2, 'jobs' => 150000.0, 'transport' => 20000.0, 'total' => 170000.0])
        ->and($rekap[0]['label'])->toContain('3 Nov 2026')
        ->and($rekap[1])->toMatchArray(['count' => 1, 'total' => 70000.0]);
});

test('LAP-007: rekap per minggu (Senin-Minggu, dipotong ke periode) dan per bulan', function () {
    $this->travelTo('2026-11-20 12:00:00');
    lapServis('2026-10-30 10:00:00', 10000); // Jumat
    lapServis('2026-11-02 10:00:00', 20000); // Senin minggu berikutnya
    lapServis('2026-11-08 10:00:00', 30000); // Minggu, masih minggu yang sama

    $metrics = lapBuka('29/10/2026 - 20/11/2026')->instance()->metrics();
    $minggu = $metrics->recap('week');
    $bulan = $metrics->recap('month');

    expect(array_column($minggu, 'total'))->toBe([10000.0, 50000.0])
        ->and($minggu[0]['label'])->toBe('29 Okt - 1 Nov')
        ->and($minggu[1]['label'])->toBe('2 Nov - 8 Nov')
        ->and(array_column($bulan, 'label'))->toBe(['Oktober 2026', 'November 2026'])
        ->and(array_column($bulan, 'total'))->toBe([10000.0, 50000.0]);
});

test('LAP-008: jumlah rekap sama dengan uang masuk di ringkasan, untuk semua pilihan rekap', function (string $unit) {
    $this->travelTo('2026-11-20 12:00:00');
    lapServis('2026-11-02 10:00:00', 120000);
    lapServis('2026-11-10 10:00:00', 80000, ['booking_type' => BookingType::HomeService]);
    lapServis('2026-11-18 10:00:00', 45000);

    $page = lapBuka('01/11/2026 - 20/11/2026')->instance();
    $rekap = $page->metrics()->recap($unit);

    expect(array_sum(array_column($rekap, 'total')))->toBe($page->summary()['total'])
        ->and(array_sum(array_column($rekap, 'count')))->toBe($page->summary()['count']);
})->with(['day', 'week', 'month']);

// LAP-009 (tabel piutang) dihapus 21 Nov 2026 karena tabelnya dihapus, jangan dipakai ulang.

// Kartu dan grafik gaya dasbor (aturan 30).

test('LAP-010: kartu ringkasan berisi pendapatan, servis lunas, rata-rata, dan belum dibayar', function () {
    $this->travelTo('2026-11-20 12:00:00');
    lapServis('2026-11-02 10:00:00', 100000);
    lapServis('2026-11-03 10:00:00', 50000);
    lapServis('2026-10-01 10:00:00', 999000); // di luar periode
    lapServis('2026-11-04 10:00:00', 70000, ['paid_at' => null]);

    $kartu = collect(\App\Filament\Widgets\Laporan\LaporanKpi::cards(['range' => '01/11/2026 - 20/11/2026']))->pluck('value', 'label');

    expect($kartu->all())->toBe([
        'Pendapatan' => 'Rp 150.000',
        'Servis lunas' => '2',
        'Rata-rata per servis' => 'Rp 75.000',
        'Belum dibayar (1 servis)' => 'Rp 70.000',
    ]);
});

// LAP-011 (grafik jasa dan spare part paling menghasilkan) dihapus 21 Nov 2026, jangan dipakai ulang.

test('LAP-012: rekap di PDF otomatis per hari, minggu, atau bulan menurut panjang periode', function () {
    $this->travelTo('2026-11-20 12:00:00');

    expect(lapBuka('01/11/2026 - 20/11/2026')->instance()->metrics()->autoUnit())->toBe('day')
        ->and(lapBuka('01/09/2026 - 20/11/2026')->instance()->metrics()->autoUnit())->toBe('week')
        ->and(lapBuka('01/01/2026 - 20/11/2026')->instance()->metrics()->autoUnit())->toBe('month');
});
