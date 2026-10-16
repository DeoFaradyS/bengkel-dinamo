<?php

use App\Support\Distance\OrsCalculator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => config(['services.ors.key' => 'test-key']));

function orsOk(float $meters)
{
    return Http::response(['routes' => [['summary' => ['distance' => $meters, 'duration' => 900]]]], 200);
}

it('ORS-001: respons sukses, meter diubah ke km', function () {
    Http::fake(['api.openrouteservice.org/*' => orsOk(10234.5)]);

    $d = (new OrsCalculator())->distanceKm(-7.2575, 112.7521, -7.30, 112.78);

    expect($d)->toEqualWithDelta(10.2345, 0.0001);
});

it('ORS-002: request benar (POST, header key, koordinat urutan lng lalu lat)', function () {
    Http::fake(['api.openrouteservice.org/*' => orsOk(5000)]);

    (new OrsCalculator())->distanceKm(-7.2575, 112.7521, -7.30, 112.78);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/v2/directions/driving-car')
            && $request->hasHeader('Authorization', 'test-key')
            && $request->data()['coordinates'] == [[112.7521, -7.2575], [112.78, -7.30]];
    });
});

it('ORS-003: status error dari ORS, hasil null', function () {
    Http::fake(['api.openrouteservice.org/*' => Http::response(['error' => 'gagal'], 500)]);

    expect((new OrsCalculator())->distanceKm(-7.2575, 112.7521, -7.30, 112.78))->toBeNull();
});

it('ORS-004: koneksi gagal atau timeout, hasil null tanpa error', function () {
    Http::fake(fn () => throw new ConnectionException('timeout'));

    expect((new OrsCalculator())->distanceKm(-7.2575, 112.7521, -7.30, 112.78))->toBeNull();
});

it('ORS-005: tanpa API key, null dan tidak memanggil ORS', function () {
    config(['services.ors.key' => null]);
    Http::fake();

    $d = (new OrsCalculator())->distanceKm(-7.2575, 112.7521, -7.30, 112.78);

    expect($d)->toBeNull();
    Http::assertNothingSent();
});

it('ORS-006: respons 200 tanpa field jarak, hasil null', function () {
    Http::fake(['api.openrouteservice.org/*' => Http::response(['routes' => []], 200)]);

    expect((new OrsCalculator())->distanceKm(-7.2575, 112.7521, -7.30, 112.78))->toBeNull();
});