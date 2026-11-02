<?php

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Models\Service;
use App\Models\WorkshopSetting;
use Illuminate\Validation\ValidationException;

// Titik bengkel ke pelanggan: udara 2,649 km.
function limWorkshop(float $max): void
{
    WorkshopSetting::create([
        'latitude' => -7.2575,
        'longitude' => 112.7521,
        'max_distance_km' => $max,
        'fuel_price' => 10000,
    ]);
}

function limBook(BookingType $type = BookingType::HomeService): Service
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

    return app(CreateBooking::class)->handle($data);
}

it('LIM-001: jarak di bawah batas, booking lolos', function () {
    limWorkshop(3);

    expect(limBook()->exists)->toBeTrue();
});

it('LIM-002: jarak lewat batas, ditolak dan tidak tersimpan', function () {
    limWorkshop(2);

    expect(fn () => limBook())->toThrow(ValidationException::class);
    expect(Service::count())->toBe(0);
});

it('LIM-003: pesan penolakan memuat jarak dan batas, terpasang di field lokasi', function () {
    limWorkshop(2);

    try {
        limBook();
        $this->fail('Seharusnya ditolak');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('latitude')
            ->and($e->errors()['latitude'][0])->toContain('2,6')->toContain('2,0');
    }
});

it('LIM-004: Ke Bengkel tidak terkena batas jarak', function () {
    limWorkshop(0.1);

    expect(limBook(BookingType::Workshop)->exists)->toBeTrue();
});