<?php

use App\Enums\BookingType;
use App\Models\Service;
use Database\Seeders\DatabaseSeeder;

it('SED-001: seeder punya servis Home Service dengan koordinat', function () {
    $this->seed(DatabaseSeeder::class);

    $home = Service::where('booking_type', BookingType::HomeService->value)->get();

    expect($home->count())->toBeGreaterThanOrEqual(3)
        ->and($home->every(fn ($s) => $s->latitude !== null && $s->longitude !== null))->toBeTrue()
        ->and($home->contains(fn ($s) => filled($s->address)))->toBeTrue()
        ->and($home->contains(fn ($s) => blank($s->address)))->toBeTrue();
});

it('SED-002: servis Ke Bengkel di seeder tanpa koordinat', function () {
    $this->seed(DatabaseSeeder::class);

    $workshop = Service::where('booking_type', BookingType::Workshop->value)->get();

    expect($workshop->count())->toBeGreaterThanOrEqual(10)
        ->and($workshop->every(fn ($s) => $s->latitude === null && $s->address === null))->toBeTrue();
});