<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\SparePart;
use App\Support\Dashboard\ServiceMetrics;
use Carbon\Carbon;

// Periode uji: 10 sampai 16 Oktober 2026 (7 hari).
function metRange(): ServiceMetrics
{
    return new ServiceMetrics(Carbon::parse('2026-10-10'), Carbon::parse('2026-10-16'));
}

function metSvc(ServiceStatus $status, string $scheduled = '2026-10-12 10:00:00', ?string $created = null, array $extra = []): Service
{
    return Service::factory()->create(array_merge([
        'status' => $status,
        'scheduled_at' => $scheduled,
        'created_at' => $created ?? $scheduled,
        'booking_type' => BookingType::Workshop,
    ], $extra));
}

function metJob(Service $service, string $name, float $price): void
{
    $type = ServiceType::factory()->create();

    $service->items()->create([
        'service_type_id' => $type->id,
        'name' => $name,
        'price' => $price,
    ]);
}

it('MET-001: booking masuk dihitung dari tanggal dibuat, batas hari ikut', function () {
    metSvc(ServiceStatus::Pending, created: '2026-10-10 00:00:00');
    metSvc(ServiceStatus::Pending, created: '2026-10-16 23:59:00');
    metSvc(ServiceStatus::Pending, created: '2026-10-09 23:59:00');
    metSvc(ServiceStatus::Pending, created: '2026-10-17 00:00:00');

    expect(metRange()->bookingsCount())->toBe(2);
});

it('MET-002: servis selesai hanya status Selesai dengan jadwal di periode', function () {
    metSvc(ServiceStatus::Completed, '2026-10-12 10:00:00');
    metSvc(ServiceStatus::Completed, '2026-10-20 10:00:00');
    metSvc(ServiceStatus::Confirmed, '2026-10-12 10:00:00');

    expect(metRange()->completedCount())->toBe(1);
});

it('MET-003: pendapatan = jasa + spare part dari servis Selesai di periode saja', function () {
    $a = metSvc(ServiceStatus::Completed, '2026-10-12 10:00:00');
    metJob($a, 'Servis dinamo', 150000);
    metJob($a, 'Ganti brush', 50000);
    $a->serviceParts()->create([
        'spare_part_id' => SparePart::factory()->create()->id,
        'quantity' => 2,
        'unit_price' => 100000,
    ]);

    $outside = metSvc(ServiceStatus::Completed, '2026-10-20 10:00:00');
    metJob($outside, 'Di luar periode', 999999);

    $pending = metSvc(ServiceStatus::Pending, '2026-10-12 10:00:00');
    metJob($pending, 'Belum selesai', 777777);

    expect(metRange()->revenue())->toEqualWithDelta(400000, 0.01);
});

it('MET-004: rata-rata nilai servis, nol kalau belum ada yang selesai', function () {
    expect(metRange()->averageValue())->toBe(0.0);

    metJob(metSvc(ServiceStatus::Completed, '2026-10-12 10:00:00'), 'A', 400000);
    metJob(metSvc(ServiceStatus::Completed, '2026-10-13 10:00:00'), 'B', 200000);

    expect(metRange()->averageValue())->toEqualWithDelta(300000, 0.01);
});

it('MET-005: tingkat pembatalan = (batal + ditolak) dibagi booking masuk', function () {
    expect(metRange()->cancellationRate())->toBe(0.0);

    metSvc(ServiceStatus::Pending);
    metSvc(ServiceStatus::Completed);
    metSvc(ServiceStatus::Cancelled);
    metSvc(ServiceStatus::Rejected);

    expect(metRange()->cancellationRate())->toBe(50.0);
});

it('MET-006: periode sebelumnya sama panjang dan menempel di depan', function () {
    $prev = metRange()->previous();

    expect($prev->from()->toDateString())->toBe('2026-10-03')
        ->and($prev->to()->toDateString())->toBe('2026-10-09');

    $single = (new ServiceMetrics(Carbon::parse('2026-10-16'), Carbon::parse('2026-10-16')))->previous();

    expect($single->from()->toDateString())->toBe('2026-10-15')
        ->and($single->to()->toDateString())->toBe('2026-10-15');
});

it('MET-007: tren per hari, satu entri untuk tiap tanggal di periode', function () {
    metSvc(ServiceStatus::Pending, '2026-10-10 09:00:00');
    metSvc(ServiceStatus::Pending, '2026-10-10 11:00:00');
    metSvc(ServiceStatus::Completed, '2026-10-12 10:00:00');

    $trend = metRange()->perDay();

    expect($trend['dates'])->toHaveCount(7)
        ->and($trend['dates'][0])->toBe('2026-10-10')
        ->and($trend['dates'][6])->toBe('2026-10-16')
        ->and($trend['bookings'])->toBe([2, 0, 1, 0, 0, 0, 0])
        ->and($trend['completed'])->toBe([0, 0, 1, 0, 0, 0, 0]);
});

it('MET-008: komposisi status lengkap, status kosong bernilai nol', function () {
    metSvc(ServiceStatus::Pending);
    metSvc(ServiceStatus::Pending);
    metSvc(ServiceStatus::Completed);

    expect(metRange()->statusBreakdown())->toEqual([
        'pending' => 2,
        'confirmed' => 0,
        'in_progress' => 0,
        'completed' => 1,
        'cancelled' => 0,
        'rejected' => 0,
    ]);
});

it('MET-009: komposisi Home Service dan Ke Bengkel', function () {
    metSvc(ServiceStatus::Pending);
    metSvc(ServiceStatus::Pending);
    metSvc(ServiceStatus::Pending, extra: ['booking_type' => BookingType::HomeService]);

    expect(metRange()->typeBreakdown())->toEqual(['workshop' => 2, 'home_service' => 1]);
});

it('MET-010: jasa terlaris dari servis Selesai di periode, urut terbanyak', function () {
    $a = metSvc(ServiceStatus::Completed);
    metJob($a, 'Servis dinamo', 100000);
    metJob($a, 'Rewinding', 300000);
    metJob(metSvc(ServiceStatus::Completed), 'Servis dinamo', 100000);
    metJob(metSvc(ServiceStatus::Completed), 'Servis dinamo', 100000);
    metJob(metSvc(ServiceStatus::Pending), 'Servis dinamo', 100000);

    expect(metRange()->topServices())->toBe(['Servis dinamo' => 3, 'Rewinding' => 1])
        ->and(metRange()->topServices(1))->toBe(['Servis dinamo' => 3]);
});

it('MET-011: spare part terlaris diurutkan menurut jumlah terpakai', function () {
    $brush = SparePart::factory()->create(['name' => 'Brush']);
    $bearing = SparePart::factory()->create(['name' => 'Bearing']);

    $a = metSvc(ServiceStatus::Completed);
    $a->serviceParts()->create(['spare_part_id' => $brush->id, 'quantity' => 2, 'unit_price' => 10000]);
    $a->serviceParts()->create(['spare_part_id' => $bearing->id, 'quantity' => 1, 'unit_price' => 20000]);

    $b = metSvc(ServiceStatus::Completed);
    $b->serviceParts()->create(['spare_part_id' => $brush->id, 'quantity' => 3, 'unit_price' => 10000]);

    expect(metRange()->topParts())->toBe(['Brush' => 5, 'Bearing' => 1]);
});

it('MET-012: servis yang dihapus tidak ikut dihitung', function () {
    $s = metSvc(ServiceStatus::Completed);
    metJob($s, 'Servis dinamo', 100000);
    $s->delete();

    $m = metRange();

    expect($m->bookingsCount())->toBe(0)
        ->and($m->completedCount())->toBe(0)
        ->and($m->revenue())->toBe(0.0)
        ->and($m->topServices())->toBe([]);
});