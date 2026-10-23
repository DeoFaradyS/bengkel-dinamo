<?php

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Support\Booking\Slots;
use Illuminate\Support\Carbon;

beforeEach(fn () => $this->travelTo(Carbon::parse('2026-10-23 08:00')));

it('EXP-001: Menunggu lewat 24 jam jadi Kedaluwarsa', function () {
    $service = Service::factory()->create([
        'status' => ServiceStatus::Pending,
        'created_at' => now()->subHours(25),
        'scheduled_at' => now()->addDays(3)->setTime(9, 0),
    ]);

    $this->artisan('bookings:expire')->assertSuccessful();

    expect($service->fresh()->status)->toBe(ServiceStatus::Expired);
});

it('EXP-002: Menunggu yang jam janjinya sudah tiba jadi Kedaluwarsa', function () {
    $service = Service::factory()->create([
        'status' => ServiceStatus::Pending,
        'created_at' => now()->subMinutes(30),
        'scheduled_at' => now()->subMinute(),
    ]);

    $this->artisan('bookings:expire')->assertSuccessful();

    expect($service->fresh()->status)->toBe(ServiceStatus::Expired);
});

it('EXP-003: Menunggu yang masih baru dan jadwalnya jauh tidak disentuh', function () {
    $service = Service::factory()->create([
        'status' => ServiceStatus::Pending,
        'created_at' => now()->subHours(2),
        'scheduled_at' => now()->addDays(3)->setTime(9, 0),
    ]);

    $this->artisan('bookings:expire')->assertSuccessful();

    expect($service->fresh()->status)->toBe(ServiceStatus::Pending);
});

it('EXP-004: status selain Menunggu tidak disentuh', function () {
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'created_at' => now()->subHours(25),
        'scheduled_at' => now()->addMinutes(30),
    ]);

    $this->artisan('bookings:expire')->assertSuccessful();

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
});

it('EXP-005: booking kedaluwarsa melepas slotnya', function () {
    $slot = now()->addDay()->setTime(9, 0);
    Service::factory()->create([
        'status' => ServiceStatus::Pending,
        'created_at' => now()->subHours(25),
        'scheduled_at' => $slot,
    ]);

    expect(Slots::isAvailable($slot))->toBeFalse();

    $this->artisan('bookings:expire');

    expect(Slots::isAvailable($slot))->toBeTrue();
});