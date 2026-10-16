<?php

namespace App\Support\Distance;

/**
 * Jarak garis lurus dua titik koordinat di permukaan bumi (rumus Haversine).
 * Hasil dalam kilometer, memakai radius rata-rata bumi 6371 km.
 */
final class HaversineCalculator
{
    private const EARTH_RADIUS_KM = 6371.0;

    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * asin(sqrt($a));
    }
}