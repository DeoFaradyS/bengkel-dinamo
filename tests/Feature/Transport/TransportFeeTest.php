<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\WorkshopSetting;

beforeEach(function () {
    WorkshopSetting::create([
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
        'max_distance_km' => 20,
        'fuel_price' => 10000,
    ]);
});

function makeService(array $override = []): Service
{
    return Service::create(array_merge([
        'customer_name' => 'Budi',
        'phone' => '081234567890',
        'complaint' => 'Dinamo panas',
        'scheduled_at' => now(),
        'status' => ServiceStatus::Pending,
        'booking_type' => BookingType::HomeService,
        'distance_km' => 5,
    ], $override));
}

it('TRN-001: 5 km pulang-pergi, ongkos Rp2.500', function () {
    $service = makeService();

    expect($service->transport_fee)->toBe(2500)
        ->and($service->fuel_price)->toBe(10000);
});

it('TRN-002: hasil dibulatkan ke atas per Rp500', function () {
    // 4,3 km x 2 / 40 x 10.000 = 2.150 -> 2.500
    expect(makeService(['distance_km' => 4.3])->transport_fee)->toBe(2500);
});

it('TRN-003: servis di bengkel, ongkos 0', function () {
    $service = makeService(['booking_type' => BookingType::Workshop]);

    expect($service->transport_fee)->toBe(0)
        ->and($service->fuel_price)->toBeNull();
});

it('TRN-004: jarak kosong, ongkos 0', function () {
    expect(makeService(['distance_km' => null])->transport_fee)->toBe(0);
});

it('TRN-005: harga BBM naik, servis lama tetap', function () {
    $service = makeService();

    WorkshopSetting::first()->update(['fuel_price' => 15000]);
    $service->update(['complaint' => 'Diubah']);

    expect($service->fresh()->transport_fee)->toBe(2500)
        ->and($service->fresh()->fuel_price)->toBe(10000);
});

it('TRN-006: jarak berubah, ongkos dihitung ulang dengan harga BBM terbaru', function () {
    $service = makeService();

    WorkshopSetting::first()->update(['fuel_price' => 15000]);
    $service->update(['distance_km' => 10]);

    // 10 x 2 / 40 x 15.000 = 7.500
    expect($service->fresh()->transport_fee)->toBe(7500)
        ->and($service->fresh()->fuel_price)->toBe(15000);
});

it('TRN-007: pindah ke bengkel lalu balik Home Service, dihitung ulang', function () {
    $service = makeService();

    $service->update(['booking_type' => BookingType::Workshop]);
    expect($service->fresh()->transport_fee)->toBe(0);

    $service->update(['booking_type' => BookingType::HomeService]);
    expect($service->fresh()->transport_fee)->toBe(2500);
});

it('TRN-008: Home Service tanpa lokasi di bengkel, ongkos 0 dan tidak error', function () {
    $data = \App\Models\Service::withDistance([
        'booking_type' => BookingType::Workshop,
        'latitude' => null,
        'longitude' => null,
    ]);

    expect($data['distance_km'])->toBeNull();
});