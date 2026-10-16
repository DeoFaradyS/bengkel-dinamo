<?php

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Enums\DistanceMethod;
use App\Models\Service;
use App\Models\WorkshopSetting;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => config(['services.ors.key' => 'test-key']));

function bkdWorkshop(DistanceMethod $method): void
{
    WorkshopSetting::create([
        'latitude' => -7.2575,
        'longitude' => 112.7521,
        'max_distance_km' => 7,
        'price_per_km' => 0,
        'distance_method' => $method,
    ]);
}

function bkdFakeOrs(int $status = 200): void
{
    Http::fake(['api.openrouteservice.org/*' => $status === 200
        ? Http::response(['routes' => [['summary' => ['distance' => 4090.7]]]], 200)
        : Http::response([], $status)]);
}

function bkdBook(BookingType $type = BookingType::HomeService): Service
{
    $data = [
        'customer_name' => 'Pak Budi',
        'phone' => '081234567890',
        'complaint' => 'Dinamo tidak mau engkol',
        'scheduled_at' => now()->addDay(),
        'booking_type' => $type,
    ];

    if ($type === BookingType::HomeService) {
        $data['latitude'] = -7.2800;
        $data['longitude'] = 112.7600;
    }

    return app(CreateBooking::class)->handle($data)->fresh();
}

it('BKD-001: Home Service metode Haversine, simpan dua jarak, resmi = udara', function () {
    bkdWorkshop(DistanceMethod::Haversine);
    bkdFakeOrs();

    $s = bkdBook();

    expect($s->distance_air_km)->toEqualWithDelta(2.649, 0.001)
        ->and($s->distance_road_km)->toEqualWithDelta(4.091, 0.001)
        ->and($s->distance_km)->toEqualWithDelta(2.649, 0.001)
        ->and($s->distance_method)->toBe(DistanceMethod::Haversine)
        ->and($s->distance_estimated)->toBeFalse();
});

it('BKD-002: Home Service metode ORS, resmi = jarak jalan', function () {
    bkdWorkshop(DistanceMethod::Ors);
    bkdFakeOrs();

    $s = bkdBook();

    expect($s->distance_km)->toEqualWithDelta(4.091, 0.001)
        ->and($s->distance_method)->toBe(DistanceMethod::Ors)
        ->and($s->distance_estimated)->toBeFalse();
});

it('BKD-003: metode ORS tapi ORS gagal, booking tetap masuk, resmi = udara, ditandai perkiraan', function () {
    bkdWorkshop(DistanceMethod::Ors);
    bkdFakeOrs(500);

    $s = bkdBook();

    expect($s->distance_road_km)->toBeNull()
        ->and($s->distance_km)->toEqualWithDelta(2.649, 0.001)
        ->and($s->distance_estimated)->toBeTrue();
});

it('BKD-004: Ke Bengkel tidak menghitung jarak dan tidak memanggil ORS', function () {
    bkdWorkshop(DistanceMethod::Haversine);
    Http::fake();

    $s = bkdBook(BookingType::Workshop);

    expect($s->distance_km)->toBeNull()
        ->and($s->distance_method)->toBeNull();
    Http::assertNothingSent();
});

it('BKD-005: data bengkel belum ada, booking tetap masuk tanpa jarak', function () {
    bkdFakeOrs();

    $s = bkdBook();

    expect($s->exists)->toBeTrue()
        ->and($s->distance_km)->toBeNull();
});