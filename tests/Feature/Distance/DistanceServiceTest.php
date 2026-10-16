<?php

use App\Enums\DistanceMethod;
use App\Support\Distance\DistanceService;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => config(['services.ors.key' => 'test-key']));

function measureFor(DistanceMethod $method)
{
    return app(DistanceService::class)->measure(-7.2575, 112.7521, -7.2800, 112.7600, $method);
}

function fakeOrsRoad(float $meters)
{
    Http::fake(['api.openrouteservice.org/*' => Http::response(['routes' => [['summary' => ['distance' => $meters]]]], 200)]);
}

it('DST-001: metode Haversine, jarak resmi = udara, jarak jalan tetap tersimpan', function () {
    fakeOrsRoad(4090.7);

    $r = measureFor(DistanceMethod::Haversine);

    expect($r->airKm)->toEqualWithDelta(2.6493, 0.001)
        ->and($r->roadKm)->toEqualWithDelta(4.0907, 0.0001)
        ->and($r->officialKm)->toBe($r->airKm)
        ->and($r->estimated)->toBeFalse();
});

it('DST-002: metode ORS, jarak resmi = jarak jalan', function () {
    fakeOrsRoad(4090.7);

    $r = measureFor(DistanceMethod::Ors);

    expect($r->officialKm)->toEqualWithDelta(4.0907, 0.0001)
        ->and($r->airKm)->toEqualWithDelta(2.6493, 0.001)
        ->and($r->estimated)->toBeFalse();
});

it('DST-003: metode ORS tapi ORS gagal, jatuh ke udara dan ditandai perkiraan', function () {
    Http::fake(['api.openrouteservice.org/*' => Http::response([], 500)]);

    $r = measureFor(DistanceMethod::Ors);

    expect($r->roadKm)->toBeNull()
        ->and($r->officialKm)->toBe($r->airKm)
        ->and($r->estimated)->toBeTrue();
});

it('DST-004: metode Haversine dan ORS gagal, tetap normal bukan perkiraan', function () {
    Http::fake(['api.openrouteservice.org/*' => Http::response([], 500)]);

    $r = measureFor(DistanceMethod::Haversine);

    expect($r->roadKm)->toBeNull()
        ->and($r->officialKm)->toBe($r->airKm)
        ->and($r->estimated)->toBeFalse();
});

it('DST-005: ORS selalu dipanggil walau metode Haversine, demi data perbandingan', function () {
    fakeOrsRoad(4090.7);

    measureFor(DistanceMethod::Haversine);

    Http::assertSentCount(1);
});