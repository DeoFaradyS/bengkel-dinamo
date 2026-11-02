<?php

use App\Models\Service;
use Database\Seeders\DatabaseSeeder;

it('SDR-001: servis Home Service dari seeder punya jarak, Ke Bengkel tidak', function () {
    $this->seed(DatabaseSeeder::class);

    $home = Service::where('booking_type', 'home_service')->get();
    $workshop = Service::where('booking_type', 'workshop')->get();

    expect($home)->not->toBeEmpty();

    foreach ($home as $s) {
        expect($s->distance_km)->toBeGreaterThan(0);
    }

    foreach ($workshop as $s) {
        expect($s->distance_km)->toBeNull();
    }
});