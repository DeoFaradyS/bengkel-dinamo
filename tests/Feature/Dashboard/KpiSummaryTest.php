<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\KpiOverview;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\SparePart;
use App\Models\User;
use App\Support\Dashboard\KpiSummary;
use App\Support\Dashboard\ServiceMetrics;
use Carbon\Carbon;
use Livewire\Livewire;

// Periode uji: 10 sampai 16 Oktober 2026. Periode lalu: 3 sampai 9 Oktober.
function kpiCards(): array
{
    $cards = KpiSummary::make(new ServiceMetrics(Carbon::parse('2026-10-10'), Carbon::parse('2026-10-16')));

    return collect($cards)->keyBy('key')->all();
}

function kpiSvc(ServiceStatus $status, string $date): Service
{
    return Service::factory()->create([
        'status' => $status,
        'scheduled_at' => $date.' 10:00:00',
        'created_at' => $date.' 10:00:00',
        'booking_type' => BookingType::Workshop,
    ]);
}

function kpiJob(Service $service, float $price): void
{
    $service->items()->create([
        'service_type_id' => ServiceType::factory()->create()->id,
        'name' => 'Servis dinamo',
        'price' => $price,
    ]);
}

it('KPI-001: empat kartu dengan urutan tetap', function () {
    expect(array_keys(kpiCards()))->toBe(['revenue', 'completed', 'bookings', 'pending']);
});

it('KPI-002: booking naik dibanding periode lalu', function () {
    kpiSvc(ServiceStatus::Pending, '2026-10-05');
    kpiSvc(ServiceStatus::Pending, '2026-10-11');
    kpiSvc(ServiceStatus::Pending, '2026-10-12');

    $c = kpiCards()['bookings'];

    expect($c['value'])->toBe('2')
        ->and($c['description'])->toBe('Naik 100% dari periode lalu')
        ->and($c['trend'])->toBe('up')
        ->and($c['positive'])->toBeTrue();
});

it('KPI-003: booking turun dibanding periode lalu', function () {
    foreach (['04', '05', '06', '07'] as $d) {
        kpiSvc(ServiceStatus::Pending, "2026-10-{$d}");
    }
    foreach (['11', '12', '13'] as $d) {
        kpiSvc(ServiceStatus::Pending, "2026-10-{$d}");
    }

    $c = kpiCards()['bookings'];

    expect($c['description'])->toBe('Turun 25% dari periode lalu')
        ->and($c['trend'])->toBe('down')
        ->and($c['positive'])->toBeFalse();
});

it('KPI-004: periode lalu kosong, belum ada pembanding', function () {
    kpiSvc(ServiceStatus::Pending, '2026-10-11');
    kpiSvc(ServiceStatus::Pending, '2026-10-12');

    $c = kpiCards()['bookings'];

    expect($c['description'])->toBeNull()
        ->and($c['trend'])->toBeNull()
        ->and($c['positive'])->toBeNull();
});

it('KPI-005: dua periode sama-sama kosong, sama dengan periode lalu', function () {
    $c = kpiCards()['bookings'];

    expect($c['value'])->toBe('0')
        ->and($c['description'])->toBe('Sama dengan periode lalu')
        ->and($c['trend'])->toBe('flat')
        ->and($c['positive'])->toBeNull();
});

it('KPI-006: pendapatan dalam rupiah, jasa + spare part, dibanding periode lalu', function () {
    $now = kpiSvc(ServiceStatus::Completed, '2026-10-12');
    kpiJob($now, 150000);
    kpiJob($now, 50000);
    $now->serviceParts()->create([
        'spare_part_id' => SparePart::factory()->create()->id,
        'quantity' => 2,
        'unit_price' => 100000,
    ]);

    kpiJob(kpiSvc(ServiceStatus::Completed, '2026-10-05'), 200000);

    $c = kpiCards()['revenue'];

    expect($c['value'])->toBe('Rp 400.000')
        ->and($c['description'])->toBe('Naik 100% dari periode lalu');
});

it('KPI-007: menunggu konfirmasi dihitung saat ini, tidak ikut periode', function () {
    kpiSvc(ServiceStatus::Pending, '2026-09-01');   // di luar periode, tetap dihitung
    kpiSvc(ServiceStatus::Pending, '2026-10-12');
    kpiSvc(ServiceStatus::Pending, '2026-10-20');   // di luar periode, tetap dihitung
    kpiSvc(ServiceStatus::Confirmed, '2026-10-12'); // bukan Pending, tidak dihitung

    expect(kpiCards()['pending']['value'])->toBe('3');
});

it('KPI-008: kartu menunggu konfirmasi tanpa tren, ada keterangan', function () {
    $c = kpiCards()['pending'];

    expect($c['description'])->toBe('Saat ini, tidak ikut filter')
        ->and($c['trend'])->toBeNull()
        ->and($c['positive'])->toBeNull();
});


it('KPI-010: widget menampilkan keempat kartu', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(KpiOverview::class)
        ->assertSee('Pendapatan')
        ->assertSee('Servis selesai')
        ->assertSee('Booking masuk')
        ->assertSee('Menunggu konfirmasi')
        ->assertDontSee('Rata-rata nilai servis')
        ->assertDontSee('Tingkat pembatalan');
});

it('KPI-011: dashboard memuat widget KPI', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Dashboard::class)->assertSeeLivewire(KpiOverview::class);
});