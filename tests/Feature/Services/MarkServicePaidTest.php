<?php

use App\Actions\MarkServicePaid;
use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\Service;

// Tandai Lunas. Kode modul: LNS.
// Letakkan di tests/Feature/Services/MarkServicePaidTest.php.

function servisLunas(ServiceStatus $status = ServiceStatus::Completed): Service
{
    return Service::create([
        'customer_name' => 'Pelanggan',
        'customer_phone' => '081200000000',
        'complaint' => 'Dinamo starter bunyi kasar',
        'scheduled_at' => now()->subDay(),
        'status' => $status,
        'booking_type' => BookingType::Workshop,
    ]);
}

test('LNS-001: servis Selesai yang belum lunas ditandai lunas, paid_at terisi', function () {
    $service = servisLunas();

    expect(app(MarkServicePaid::class)->handle($service))->toBeTrue()
        ->and($service->fresh()->paid_at)->not->toBeNull();
});

test('LNS-002: servis yang belum Selesai ditolak, paid_at tetap NULL', function (ServiceStatus $status) {
    $service = servisLunas($status);

    expect(app(MarkServicePaid::class)->handle($service))->toBeFalse()
        ->and($service->fresh()->paid_at)->toBeNull();
})->with([
    'menunggu' => ServiceStatus::Pending,
    'dikerjakan' => ServiceStatus::InProgress,
    'dibatalkan' => ServiceStatus::Cancelled,
]);

test('LNS-003: servis yang sudah lunas tidak bisa ditandai dua kali, paid_at tidak tertimpa', function () {
    $service = servisLunas();
    app(MarkServicePaid::class)->handle($service);
    $pertama = $service->fresh()->paid_at;

    $this->travel(2)->hours();

    expect(app(MarkServicePaid::class)->handle($service))->toBeFalse()
        ->and($service->fresh()->paid_at->equalTo($pertama))->toBeTrue();
});

test('LNS-004: paid_at tidak bisa diisi lewat mass assignment', function () {
    $service = Service::create([
        'customer_name' => 'Pelanggan',
        'customer_phone' => '081200000001',
        'complaint' => 'Keluhan',
        'scheduled_at' => now()->subDay(),
        'status' => ServiceStatus::Completed,
        'booking_type' => BookingType::Workshop,
        'paid_at' => now(),
    ]);

    expect($service->fresh()->paid_at)->toBeNull();
});