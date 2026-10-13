<?php

use App\Actions\CreateBooking;
use App\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Validation\ValidationException;

function bookingData(array $override = []): array
{
    return array_merge([
        'customer_name' => 'Bu Sari',
        'phone' => '081234500001',
        'complaint' => 'Dinamo panas',
        'scheduled_at' => now()->addDay(),
    ], $override);
}

it('BKG-001: booking baru masuk status Menunggu Konfirmasi', function () {
    $service = app(CreateBooking::class)->handle(bookingData());

    expect($service->status)->toBe(ServiceStatus::Pending)
        ->and(Service::count())->toBe(1);
});

it('BKG-002: HP disimpan dalam format 08...', function () {
    $service = app(CreateBooking::class)->handle(bookingData(['phone' => '+62 812 3450 0001']));

    expect($service->phone)->toBe('081234500001');
});

it('BKG-003: HP punya servis aktif ditolak, format apa pun', function (string $phone) {
    Service::factory()->create(['phone' => '081234500001']);

    expect(fn () => app(CreateBooking::class)->handle(bookingData(['phone' => $phone])))
        ->toThrow(ValidationException::class);

    expect(Service::count())->toBe(1);
})->with([
    'format 08' => ['081234500001'],
    'format +62' => ['+6281234500001'],
    'format 62' => ['6281234500001'],
]);

it('BKG-004: HP dengan booking Menunggu Konfirmasi juga ditolak', function () {
    app(CreateBooking::class)->handle(bookingData());

    expect(fn () => app(CreateBooking::class)->handle(bookingData()))
        ->toThrow(ValidationException::class);

    expect(Service::count())->toBe(1);
});

it('BKG-005: HP dengan servis Selesai boleh booking lagi', function () {
    Service::factory()->completed()->create(['phone' => '081234500001']);

    $service = app(CreateBooking::class)->handle(bookingData());

    expect($service->status)->toBe(ServiceStatus::Pending)
        ->and(Service::count())->toBe(2);
});

it('BKG-006: HP dengan servis yang di-soft-delete boleh booking lagi', function () {
    Service::factory()->create(['phone' => '081234500001'])->delete();

    app(CreateBooking::class)->handle(bookingData());

    expect(Service::count())->toBe(1);
});