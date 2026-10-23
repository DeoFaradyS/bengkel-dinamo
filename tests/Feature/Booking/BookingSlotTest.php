<?php

use App\Actions\CreateBooking;
use App\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

beforeEach(fn () => $this->travelTo(Carbon::parse('2026-10-23 06:00')));

function slotslotslotslotBookingData(string $when = '2026-10-23 09:00', ?string $phone = null): array
{
    return [
        'customer_name' => 'Budi',
        'phone' => $phone ?? fake()->unique()->numerify('08##########'),
        'complaint' => 'Dinamo mati',
        'scheduled_at' => $when,
        'booking_type' => 'workshop',
    ];
}

it('SLB-001: booking di slot kosong berhasil', function () {
    $service = app(CreateBooking::class)->handle(slotslotslotslotBookingData());

    expect($service->scheduled_at->format('Y-m-d H:i'))->toBe('2026-10-23 09:00')
        ->and($service->status)->toBe(ServiceStatus::Pending);
});

it('SLB-002: slot yang sudah terisi ditolak', function () {
    app(CreateBooking::class)->handle(slotslotslotslotBookingData());

    expect(fn () => app(CreateBooking::class)->handle(slotslotslotslotBookingData()))
        ->toThrow(ValidationException::class);

    expect(Service::count())->toBe(1);
});

it('SLB-003: servis final melepaskan slot', function (ServiceStatus $status) {
    $first = app(CreateBooking::class)->handle(slotslotslotslotBookingData());
    $first->update(['status' => $status]);

    app(CreateBooking::class)->handle(slotslotslotslotBookingData());

    expect(Service::count())->toBe(2);
})->with([
    'selesai' => [ServiceStatus::Completed],
    'dibatalkan' => [ServiceStatus::Cancelled],
    'ditolak' => [ServiceStatus::Rejected],
]);

it('SLB-004: jam bukan slot ditolak', function (string $when) {
    expect(fn () => app(CreateBooking::class)->handle(slotslotslotslotBookingData($when)))
        ->toThrow(ValidationException::class);
})->with([
    'istirahat' => '2026-10-23 12:00',
    'bukan awal jam' => '2026-10-23 09:30',
    'sebelum buka' => '2026-10-24 06:00',
    'jam tutup' => '2026-10-24 17:00',
]);

it('SLB-005: terlalu dekat atau terlalu jauh ditolak', function (string $when) {
    expect(fn () => app(CreateBooking::class)->handle(slotslotslotslotBookingData($when)))
        ->toThrow(ValidationException::class);
})->with([
    'sudah lewat' => '2026-10-22 09:00',
    'kurang dari 1 jam' => '2026-10-23 06:30',
    'lebih dari 14 hari' => '2026-11-10 09:00',
]);
