<?php

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Models\Service;
use App\Models\WorkshopSetting;

function bkdWorkshop(): void
{
    WorkshopSetting::create([
        'latitude' => -7.2575,
        'longitude' => 112.7521,
        'max_distance_km' => 7,
        'fuel_price' => 10000,
    ]);
}

function bkdBook(BookingType $type = BookingType::HomeService): Service
{
    $data = [
        'customer_name' => 'Pak Budi',
        'phone' => '081234567890',
        'complaint' => 'Dinamo tidak mau engkol',
        'scheduled_at' => nextSlot(),
        'booking_type' => $type,
    ];

    if ($type === BookingType::HomeService) {
        $data['latitude'] = -7.2800;
        $data['longitude'] = 112.7600;
    }

    return app(CreateBooking::class)->handle($data)->fresh();
}

it('BKD-001: Home Service menyimpan jarak udara', function () {
    bkdWorkshop();

    expect(bkdBook()->distance_km)->toEqualWithDelta(2.649, 0.001);
});

it('BKD-002: Ke Bengkel tidak menghitung jarak', function () {
    bkdWorkshop();

    expect(bkdBook(BookingType::Workshop)->distance_km)->toBeNull();
});

it('BKD-003: data bengkel belum ada, booking tetap masuk tanpa jarak', function () {
    $s = bkdBook();

    expect($s->exists)->toBeTrue()
        ->and($s->distance_km)->toBeNull();
});