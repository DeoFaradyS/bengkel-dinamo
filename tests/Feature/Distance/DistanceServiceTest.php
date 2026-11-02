<?php

use App\Support\Distance\DistanceService;

function measureAir(float $toLat = -7.2800, float $toLng = 112.7600)
{
    return app(DistanceService::class)->measure(-7.2575, 112.7521, $toLat, $toLng);
}

it('DST-001: jarak dihitung udara (Haversine)', function () {
    expect(measureAir()->airKm)->toEqualWithDelta(2.6493, 0.001);
});

it('DST-002: titik sama, jarak nol', function () {
    expect(measureAir(-7.2575, 112.7521)->airKm)->toEqualWithDelta(0.0, 0.001);
});