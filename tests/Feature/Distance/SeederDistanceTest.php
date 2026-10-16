<?php

use App\Enums\DistanceMethod;
use App\Models\Service;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Http;

it('SDR-001: servis Home Service dari seeder punya jarak, Ke Bengkel tidak', function () {
    config(['services.ors.key' => 'test-key']);
    Http::fake(['api.openrouteservice.org/*' => Http::response(
        ['routes' => [['summary' => ['distance' => 4000]]]], 200
    )]);

    $this->seed(DatabaseSeeder::class);

    $home = Service::where('booking_type', 'home_service')->get();
    $workshop = Service::where('booking_type', 'workshop')->get();

    expect($home)->not->toBeEmpty();

    foreach ($home as $s) {
        expect($s->distance_air_km)->not->toBeNull()
            ->and($s->distance_road_km)->toEqualWithDelta(4.0, 0.001)
            ->and($s->distance_km)->not->toBeNull()
            ->and($s->distance_method)->toBe(DistanceMethod::Haversine);
    }

    foreach ($workshop as $s) {
        expect($s->distance_km)->toBeNull();
    }
});