<?php

use App\Enums\DistanceMethod;
use App\Livewire\BookingForm;
use App\Models\WorkshopSetting;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(fn () => config(['services.ors.key' => 'test-key']));

// Bengkel ke pelanggan: udara 2,649 km, jalan 4,091 km.
function prvWorkshop(DistanceMethod $method, float $max = 10): void
{
    WorkshopSetting::create([
        'latitude' => -7.2575,
        'longitude' => 112.7521,
        'max_distance_km' => $max,
        'price_per_km' => 0,
        'distance_method' => $method,
    ]);
}

function prvFakeOrs(bool $ok = true): void
{
    Http::fake(['api.openrouteservice.org/*' => $ok
        ? Http::response(['routes' => [['summary' => ['distance' => 4090.7]]]], 200)
        : Http::response([], 500)]);
}

function prvPick()
{
    return Livewire::test(BookingForm::class)
        ->set('booking_type', 'home_service')
        ->set('latitude', -7.2800)
        ->set('longitude', 112.7600);
}

it('PRV-001: metode Haversine, jarak udara tampil', function () {
    prvWorkshop(DistanceMethod::Haversine);
    prvFakeOrs();

    prvPick()->call('hitungJarak')
        ->assertSet('distanceText', 'Jarak ke bengkel sekitar 2,6 km')
        ->assertSet('distanceError', null)
        ->assertSee('Jarak ke bengkel sekitar 2,6 km');
});

it('PRV-002: metode ORS, jarak jalan tampil', function () {
    prvWorkshop(DistanceMethod::Ors);
    prvFakeOrs();

    prvPick()->call('hitungJarak')
        ->assertSet('distanceText', 'Jarak ke bengkel sekitar 4,1 km');
});

it('PRV-003: lewat batas, tampil pesan di luar jangkauan, bukan jarak', function () {
    prvWorkshop(DistanceMethod::Haversine, 2);
    prvFakeOrs();

    $c = prvPick()->call('hitungJarak');

    expect($c->get('distanceText'))->toBeNull()
        ->and($c->get('distanceError'))->toContain('di luar jangkauan')->toContain('2,6')->toContain('2,0');
});

it('PRV-004: metode ORS gagal, jarak udara berlabel perkiraan', function () {
    prvWorkshop(DistanceMethod::Ors);
    prvFakeOrs(false);

    prvPick()->call('hitungJarak')
        ->assertSet('distanceText', 'Jarak ke bengkel sekitar 2,6 km (perkiraan)');
});

it('PRV-005: belum pilih lokasi, tidak ada hitungan dan tidak memanggil ORS', function () {
    prvWorkshop(DistanceMethod::Ors);
    Http::fake();

    Livewire::test(BookingForm::class)
        ->set('booking_type', 'home_service')
        ->call('hitungJarak')
        ->assertSet('distanceText', null)
        ->assertSet('distanceError', null);

    Http::assertNothingSent();
});

it('PRV-006: Ke Bengkel tidak menghitung jarak', function () {
    prvWorkshop(DistanceMethod::Ors);
    Http::fake();

    Livewire::test(BookingForm::class)
        ->set('booking_type', 'workshop')
        ->set('latitude', -7.2800)
        ->set('longitude', 112.7600)
        ->call('hitungJarak')
        ->assertSet('distanceText', null);

    Http::assertNothingSent();
});

it('PRV-007: data bengkel belum ada, diam saja tanpa error', function () {
    prvFakeOrs();

    prvPick()->call('hitungJarak')
        ->assertSet('distanceText', null)
        ->assertSet('distanceError', null);
});