<?php

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Enums\DistanceMethod;
use App\Models\Service;
use App\Models\WorkshopSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

beforeEach(fn () => config(['services.ors.key' => 'test-key']));

// Titik bengkel ke pelanggan: udara 2,649 km, jalan 4,091 km.
function limWorkshop(DistanceMethod $method, float $max): void
{
    WorkshopSetting::create([
        'latitude' => -7.2575,
        'longitude' => 112.7521,
        'max_distance_km' => $max,
        'fuel_price' => 10000,
        'distance_method' => $method,
    ]);
}

function limFakeOrs(bool $ok = true): void
{
    Http::fake(['api.openrouteservice.org/*' => $ok
        ? Http::response(['routes' => [['summary' => ['distance' => 4090.7]]]], 200)
        : Http::response([], 500)]);
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

it('LIM-001: Haversine, udara di bawah batas, booking lolos', function () {
    limWorkshop(DistanceMethod::Haversine, 3);
    limFakeOrs();

    expect(limBook()->exists)->toBeTrue();
});

it('LIM-002: Haversine, udara lewat batas, ditolak dan tidak tersimpan', function () {
    limWorkshop(DistanceMethod::Haversine, 2);
    limFakeOrs();

    expect(fn () => limBook())->toThrow(ValidationException::class);
    expect(Service::count())->toBe(0);
});

it('LIM-003: kasus utama, udara di bawah batas tapi jalan lewat, metode ORS menolak', function () {
    limWorkshop(DistanceMethod::Ors, 3);
    limFakeOrs();

    expect(fn () => limBook())->toThrow(ValidationException::class);
    expect(Service::count())->toBe(0);
});

it('LIM-004: kasus yang sama dengan metode Haversine lolos (bahan perbandingan demo)', function () {
    limWorkshop(DistanceMethod::Haversine, 3);
    limFakeOrs();

    expect(limBook()->exists)->toBeTrue();
});

it('LIM-005: metode ORS gagal, udara di bawah batas, lolos dan ditandai perkiraan', function () {
    limWorkshop(DistanceMethod::Ors, 3);
    limFakeOrs(false);

    $s = limBook();

    expect($s->exists)->toBeTrue()
        ->and($s->distance_estimated)->toBeTrue();
});

it('LIM-006: metode ORS gagal, udara sudah lewat batas, ditolak', function () {
    limWorkshop(DistanceMethod::Ors, 2);
    limFakeOrs(false);

    expect(fn () => limBook())->toThrow(ValidationException::class);
    expect(Service::count())->toBe(0);
});

it('LIM-007: pesan penolakan memuat jarak dan batas, terpasang di field lokasi', function () {
    limWorkshop(DistanceMethod::Haversine, 2);
    limFakeOrs();

    try {
        limBook();
        $this->fail('Seharusnya ditolak');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('latitude')
            ->and($e->errors()['latitude'][0])->toContain('2,6')->toContain('2,0');
    }
});

it('LIM-008: Ke Bengkel tidak terkena batas jarak', function () {
    limWorkshop(DistanceMethod::Haversine, 0.1);
    Http::fake();

    expect(limBook(BookingType::Workshop)->exists)->toBeTrue();
});