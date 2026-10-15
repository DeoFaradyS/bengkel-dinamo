<?php

use App\Enums\BookingType;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dataServis(array $extra = []): array
{
    return array_merge([
        'customer_name' => 'Budi',
        'phone' => '081234567890',
        'complaint' => 'Dinamo starter mati',
        'scheduled_at' => now()->addDay(),
    ], $extra);
}

it('BKT-001: booking_type default workshop', function () {
    $service = Service::create(dataServis())->fresh();

    expect($service->booking_type)->toBe(BookingType::Workshop);
});

it('BKT-002: home service menyimpan latitude dan longitude', function () {
    $service = Service::create(dataServis([
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
    ]))->fresh();

    expect($service->booking_type)->toBe(BookingType::HomeService)
        ->and($service->latitude)->toEqualWithDelta(-7.7174465, 0.0000001)
        ->and($service->longitude)->toEqualWithDelta(113.0755426, 0.0000001);
});

it('BKT-003: workshop boleh tanpa koordinat', function () {
    $service = Service::create(dataServis())->fresh();

    expect($service->latitude)->toBeNull()
        ->and($service->longitude)->toBeNull();
});

it('BKT-004: label BookingType', function () {
    expect(BookingType::Workshop->getLabel())->toBe('Antar ke bengkel')
        ->and(BookingType::HomeService->getLabel())->toBe('Montir datang ke lokasi');
});