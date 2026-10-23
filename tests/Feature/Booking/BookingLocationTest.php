<?php

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Livewire\BookingForm;
use App\Models\Service;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

// Halaman publik: tes ini jalan sebagai tamu, bukan admin.
beforeEach(fn () => auth()->logout());

function lokasiData(array $override = []): array
{
    return array_merge([
        'customer_name' => 'Pak Budi',
        'phone' => '081234500002',
        'complaint' => 'Dinamo starter mati',
        'scheduled_at' => nextSlot(),
    ], $override);
}

function isiFormLokasi(array $override = [])
{
    $data = array_merge([
        'customer_name' => 'Pak Budi',
        'phone' => '081234500002',
        'complaint' => 'Dinamo starter mati',
        'scheduled_at' => nextSlot()->format('Y-m-d H:i'),
    ], $override);

    $component = Livewire::test(BookingForm::class);

    foreach ($data as $key => $value) {
        $component->set($key, $value);
    }

    return $component;
}

it('LOC-001: tanpa tipe, booking default workshop', function () {
    $service = app(CreateBooking::class)->handle(lokasiData());

    expect($service->booking_type)->toBe(BookingType::Workshop)
        ->and($service->latitude)->toBeNull()
        ->and($service->longitude)->toBeNull();
});

it('LOC-002: home service dengan koordinat tersimpan', function () {
    $service = app(CreateBooking::class)->handle(lokasiData([
        'booking_type' => BookingType::HomeService->value,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
    ]))->fresh();

    expect($service->booking_type)->toBe(BookingType::HomeService)
        ->and($service->latitude)->toEqualWithDelta(-7.7174465, 0.0000001)
        ->and($service->longitude)->toEqualWithDelta(113.0755426, 0.0000001);
});

it('LOC-003: home service tanpa koordinat lengkap ditolak', function (array $koordinat) {
    expect(fn () => app(CreateBooking::class)->handle(lokasiData(array_merge(
        ['booking_type' => BookingType::HomeService->value],
        $koordinat,
    ))))->toThrow(ValidationException::class);

    expect(Service::count())->toBe(0);
})->with([
    'dua-duanya kosong' => [[]],
    'dua-duanya null' => [['latitude' => null, 'longitude' => null]],
    'hanya latitude' => [['latitude' => -7.71]],
    'hanya longitude' => [['longitude' => 113.07]],
]);

it('LOC-004: koordinat di luar jangkauan ditolak', function (array $koordinat) {
    expect(fn () => app(CreateBooking::class)->handle(lokasiData(array_merge(
        ['booking_type' => BookingType::HomeService->value],
        $koordinat,
    ))))->toThrow(ValidationException::class);

    expect(Service::count())->toBe(0);
})->with([
    'latitude 91' => [['latitude' => 91, 'longitude' => 113.07]],
    'latitude -91' => [['latitude' => -91, 'longitude' => 113.07]],
    'longitude 181' => [['latitude' => -7.71, 'longitude' => 181]],
    'longitude -181' => [['latitude' => -7.71, 'longitude' => -181]],
]);

it('LOC-005: workshop mengabaikan koordinat yang terkirim', function () {
    $service = app(CreateBooking::class)->handle(lokasiData([
        'booking_type' => BookingType::Workshop->value,
        'latitude' => -7.71,
        'longitude' => 113.07,
    ]))->fresh();

    expect($service->booking_type)->toBe(BookingType::Workshop)
        ->and($service->latitude)->toBeNull()
        ->and($service->longitude)->toBeNull();
});

it('LOC-010: form default workshop, kirim tanpa koordinat lolos', function () {
    isiFormLokasi()
        ->call('submit')
        ->assertHasNoErrors();

    expect(Service::first()->booking_type)->toBe(BookingType::Workshop);
});

it('LOC-011: form home service dengan koordinat tersimpan', function () {
    isiFormLokasi([
        'booking_type' => BookingType::HomeService->value,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
    ])
        ->call('submit')
        ->assertHasNoErrors();

    $service = Service::first();

    expect($service->booking_type)->toBe(BookingType::HomeService)
        ->and($service->latitude)->toEqualWithDelta(-7.7174465, 0.0000001);
});

it('LOC-012: form home service tanpa koordinat ditolak', function () {
    isiFormLokasi(['booking_type' => BookingType::HomeService->value])
        ->call('submit')
        ->assertHasErrors(['latitude']);

    expect(Service::count())->toBe(0);
});

it('LOC-013: form tolak tipe layanan yang tidak dikenal', function () {
    isiFormLokasi(['booking_type' => 'terbang'])
        ->call('submit')
        ->assertHasErrors(['booking_type']);

    expect(Service::count())->toBe(0);
});