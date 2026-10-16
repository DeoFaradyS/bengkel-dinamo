<?php

namespace App\Support\Distance;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Jarak lewat jalan dari OpenRouteService (profil driving-car).
 * Hasil dalam kilometer, atau null kalau ORS tidak bisa dipakai
 * (tanpa key, gagal, timeout, atau titik tidak dekat jalan).
 */
final class OrsCalculator
{
    private const URL = 'https://api.openrouteservice.org/v2/directions/driving-car';

    public function __construct(private ?string $apiKey = null)
    {
        $this->apiKey ??= config('services.ors.key');
    }

    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): ?float
    {
        if (blank($this->apiKey)) {
            return null;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $this->apiKey])
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->post(self::URL, [
                    // ORS memakai urutan [lng, lat]
                    'coordinates' => [[$lng1, $lat1], [$lng2, $lat2]],
                ]);
        } catch (Throwable) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $meters = $response->json('routes.0.summary.distance');

        return is_numeric($meters) ? $meters / 1000 : null;
    }
}