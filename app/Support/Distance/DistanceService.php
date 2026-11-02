<?php

namespace App\Support\Distance;

/**
 * Satu pintu penghitung jarak. Memakai jarak udara (Haversine).
 */
final class DistanceService
{
    public function __construct(
        private HaversineCalculator $haversine,
    ) {
    }

    public function measure(
        float $fromLat,
        float $fromLng,
        float $toLat,
        float $toLng,
    ): DistanceResult {
        return new DistanceResult(
            $this->haversine->distanceKm($fromLat, $fromLng, $toLat, $toLng),
        );
    }
}