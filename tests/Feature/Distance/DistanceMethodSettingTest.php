<?php

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Enums\DistanceMethod;
use App\Filament\Pages\JarakOngkos;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkshopSetting;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    WorkshopSetting::create([
        'latitude' => -7.2575,
        'longitude' => 112.7521,
        'max_distance_km' => 7,
        'price_per_km' => 3000,
        'distance_method' => DistanceMethod::Haversine,
    ]);
});

it('SET-001: halaman menampilkan metode yang sedang aktif', function () {
    Livewire::test(JarakOngkos::class)
        ->assertFormSet(['distance_method' => 'haversine']);
});

it('SET-002: admin ganti metode ke ORS, tersimpan', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(['distance_method' => 'ors'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(WorkshopSetting::first()->distance_method)->toBe(DistanceMethod::Ors);
});

it('SET-003: metode tidak dikenal ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(['distance_method' => 'google'])
        ->call('save')
        ->assertHasFormErrors(['distance_method']);

    expect(WorkshopSetting::first()->distance_method)->toBe(DistanceMethod::Haversine);
});

it('SET-004: skenario demo, ganti metode hanya berlaku untuk booking berikutnya', function () {
    config(['services.ors.key' => 'test-key']);
    Http::fake(['api.openrouteservice.org/*' => Http::response(
        ['routes' => [['summary' => ['distance' => 4090.7]]]], 200
    )]);

    $book = fn (string $phone) => app(CreateBooking::class)->handle([
        'customer_name' => 'Pak Budi',
        'phone' => $phone,
        'complaint' => 'Dinamo tidak mau engkol',
        'scheduled_at' => now()->addDay(),
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.2800,
        'longitude' => 112.7600,
    ])->fresh();

    $a = $book('081234567890');

    Livewire::test(JarakOngkos::class)
        ->fillForm(['distance_method' => 'ors'])
        ->call('save');

    $b = $book('081234567891');

    expect($a->distance_method)->toBe(DistanceMethod::Haversine)
        ->and($a->distance_km)->toEqualWithDelta(2.649, 0.001)
        ->and($b->distance_method)->toBe(DistanceMethod::Ors)
        ->and($b->distance_km)->toEqualWithDelta(4.091, 0.001)
        ->and(Service::find($a->id)->distance_km)->toEqualWithDelta(2.649, 0.001);
});