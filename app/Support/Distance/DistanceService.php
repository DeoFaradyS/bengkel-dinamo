<?php

namespace App\Support\Distance;

use App\Enums\DistanceMethod;

/**
 * Satu pintu penghitung jarak. Selalu menghitung jarak udara (Haversine)
 * dan jarak jalan (ORS), lalu memilih jarak resmi sesuai metode aktif.
 */
final class DistanceService
{
    public function __construct(
        private HaversineCalculator $haversine,
        private OrsCalculator $ors,
    ) {
    }

    public function measure(
        float $fromLat,
        float $fromLng,
        float $toLat,
        float $toLng,
        DistanceMethod $method,
    ): DistanceResult {
        $air = $this->haversine->distanceKm($fromLat, $fromLng, $toLat, $toLng);
        $road = $this->ors->distanceKm($fromLat, $fromLng, $toLat, $toLng);

        if ($method === DistanceMethod::Ors && $road !== null) {
            return new DistanceResult($air, $road, $road, $method, false);
        }

        // Haversine dipilih, atau ORS dipilih tapi gagal: pakai jarak udara.
        // Kalau ORS yang dipilih lalu gagal, tandai sebagai perkiraan.
        return new DistanceResult($air, $road, $air, $method, $method === DistanceMethod::Ors);
    }
}