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

function lapBuka(string $range, ?string $tipe = null)
{
    return Livewire::test(LaporanPendapatan::class)
        ->set('filters.range', $range)
        ->set('filters.tipe', $tipe ?? '');
}

function lapRentang($dari, $sampai): string
{
    return $dari->format('d/m/Y') . ' - ' . $sampai->format('d/m/Y');
}

test('LAP-001: halaman Laporan Pendapatan terbuka untuk owner', function () {
    $this->get('/admin/laporan-pendapatan')
        ->assertOk()
        ->assertSee('Rincian sumber pendapatan');
});

test('LAP-002: tabel hanya berisi servis lunas di periode, yang belum lunas atau di luar periode tidak', function () {
    $lunas = lapServis(now()->subDays(2)->setTime(10, 0)->toDateTimeString(), 100000);
    $belum = lapServis(now()->subDays(2)->setTime(11, 0)->toDateTimeString(), 200000, ['paid_at' => null]);
    $lama = lapServis(now()->subDays(40)->setTime(10, 0)->toDateTimeString(), 300000);

    lapBuka(lapRentang(now()->subDays(7), now()))
        ->assertCanSeeTableRecords([$lunas])
        ->assertCanNotSeeTableRecords([$belum, $lama]);
});

test('LAP-003: filter tipe layanan hanya menampilkan tipe itu', function () {
    $bengkel = lapServis(now()->subDay()->setTime(10, 0)->toDateTimeString(), 100000);
    $montir = lapServis(now()->subDay()->setTime(11, 0)->toDateTimeString(), 150000, ['booking_type' => BookingType::HomeService]);

    lapBuka(lapRentang(now()->subDays(7), now()), BookingType::HomeService->value)
        ->assertCanSeeTableRecords([$montir])
        ->assertCanNotSeeTableRecords([$bengkel]);
});

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
