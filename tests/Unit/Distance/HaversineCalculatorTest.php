<?php

use App\Support\Distance\HaversineCalculator;

it('HAV-001: titik sama, jarak nol', function () {
    $d = (new HaversineCalculator())->distanceKm(-7.2575, 112.7521, -7.2575, 112.7521);

    expect($d)->toBe(0.0);
});

it('HAV-002: 1 derajat bujur di ekuator sekitar 111,19 km', function () {
    $d = (new HaversineCalculator())->distanceKm(0, 0, 0, 1);

    expect($d)->toEqualWithDelta(111.19, 0.01);
});

it('HAV-003: 1 derajat lintang sekitar 111,19 km', function () {
    $d = (new HaversineCalculator())->distanceKm(-7, 112, -8, 112);

    expect($d)->toEqualWithDelta(111.19, 0.01);
});

it('HAV-004: Paris ke Frankfurt sekitar 478 km', function () {
    $d = (new HaversineCalculator())->distanceKm(48.8566, 2.3522, 50.1109, 8.6821);

    expect($d)->toEqualWithDelta(477.89, 0.01);
});

it('HAV-005: titik A ke B sama dengan B ke A', function () {
    $calc = new HaversineCalculator();

    $ab = $calc->distanceKm(-7.2459, 112.7378, -7.3798, 112.7869);
    $ba = $calc->distanceKm(-7.3798, 112.7869, -7.2459, 112.7378);

    expect($ab)->toEqualWithDelta($ba, 0.000001);
});