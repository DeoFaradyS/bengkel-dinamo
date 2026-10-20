<?php

use App\Filament\Pages\Dashboard;
use App\Models\User;
use App\Support\Dashboard\DashboardPeriod;
use Carbon\Carbon;
use Livewire\Livewire;

// Hari ini dibekukan: Minggu, 18 Oktober 2026.
beforeEach(fn () => Carbon::setTestNow('2026-10-18 14:00:00'));
afterEach(fn () => Carbon::setTestNow());

function dshRange(?array $filters): array
{
    $m = DashboardPeriod::metrics($filters);

    return [$m->from()->toDateString(), $m->to()->toDateString(), $m->days()];
}

it('DSH-001: tanpa filter, bawaannya bulan ini sampai hari ini', function () {
    expect(dshRange(null))->toBe(['2026-10-01', '2026-10-18', 18]);
});

it('DSH-002: hari ini', function () {
    expect(dshRange(['preset' => 'today']))->toBe(['2026-10-18', '2026-10-18', 1]);
});

it('DSH-003: 7 hari terakhir, hari ini ikut dihitung', function () {
    expect(dshRange(['preset' => 'last7']))->toBe(['2026-10-12', '2026-10-18', 7]);
});

it('DSH-004: bulan ini', function () {
    expect(dshRange(['preset' => 'this_month']))->toBe(['2026-10-01', '2026-10-18', 18]);
});

it('DSH-005: tahun ini, 1 Januari sampai hari ini', function () {
    expect(dshRange(['preset' => 'this_year']))->toBe(['2026-01-01', '2026-10-18', 291]);
});

it('DSH-006: rentang tanggal sendiri', function () {
    $f = ['preset' => 'custom', 'start_date' => '2026-10-03', 'end_date' => '2026-10-09'];

    expect(dshRange($f))->toBe(['2026-10-03', '2026-10-09', 7]);
});

it('DSH-007: tanggal terbalik ditukar otomatis', function () {
    $f = ['preset' => 'custom', 'start_date' => '2026-10-09', 'end_date' => '2026-10-03'];

    expect(dshRange($f))->toBe(['2026-10-03', '2026-10-09', 7]);
});

it('DSH-008: tanggal sendiri belum lengkap, kembali ke bulan ini', function () {
    $f = ['preset' => 'custom', 'start_date' => '2026-10-03', 'end_date' => null];

    expect(dshRange($f))->toBe(['2026-10-01', '2026-10-18', 18]);
});

it('DSH-009: rentang terlalu panjang dipotong 366 hari', function () {
    $f = ['preset' => 'custom', 'start_date' => '2000-01-01', 'end_date' => '2026-10-18'];

    expect(dshRange($f)[2])->toBe(366);
});

it('DSH-010: pilihan tidak dikenal kembali ke bawaan', function () {
    expect(dshRange(['preset' => 'asal']))->toBe(['2026-10-01', '2026-10-18', 18]);
});

it('DSH-011: periode pembanding menempel di depan dengan panjang sama', function () {
    $prev = DashboardPeriod::metrics(['preset' => 'last7'])->previous();

    expect($prev->from()->toDateString())->toBe('2026-10-05')
        ->and($prev->to()->toDateString())->toBe('2026-10-11');
});

it('DSH-012: halaman dashboard tampil, tombol periode menampilkan periode aktif', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Dashboard::class)->assertOk()->assertSee('Bulan ini');
});

it('DSH-013: label periode untuk pilihan bawaan dan preset', function () {
    expect(DashboardPeriod::label(null))->toBe('Bulan ini')
        ->and(DashboardPeriod::label(['preset' => 'today']))->toBe('Hari ini')
        ->and(DashboardPeriod::label(['preset' => 'this_year']))->toBe('Tahun ini')
        ->and(DashboardPeriod::label(['preset' => 'asal']))->toBe('Bulan ini');
});

it('DSH-014: label rentang sendiri berisi tanggal', function () {
    $f = ['preset' => 'custom', 'start_date' => '2026-10-03', 'end_date' => '2026-10-09'];

    expect(DashboardPeriod::label($f))->toBe('03/10/2026 - 09/10/2026');
});