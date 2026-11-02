<?php

use App\Livewire\BookingForm;
use App\Models\WorkshopSetting;
use Livewire\Livewire;

// Bengkel ke pelanggan: udara 2,649 km.
function prvWorkshop(float $max = 10): void
{
    WorkshopSetting::create([
        'latitude' => -7.2575,
        'longitude' => 112.7521,
        'max_distance_km' => $max,
        'fuel_price' => 10000,
    ]);
}

function prvPick()
{
    return Livewire::test(BookingForm::class)
        ->set('booking_type', 'home_service')
        ->set('latitude', -7.2800)
        ->set('longitude', 112.7600);
}

it('PRV-001: jarak udara tampil', function () {
    prvWorkshop();

    prvPick()->call('hitungJarak')
        ->assertSet('distanceText', 'Jarak ke bengkel sekitar 2,6 km')
        ->assertSet('distanceError', null)
        ->assertSee('Jarak ke bengkel sekitar 2,6 km');
});

it('PRV-002: lewat batas, tampil pesan di luar jangkauan, bukan jarak', function () {
    prvWorkshop(2);

    $c = prvPick()->call('hitungJarak');

    expect($c->get('distanceText'))->toBeNull()
        ->and($c->get('distanceError'))->toContain('di luar jangkauan')->toContain('2,6')->toContain('2,0');
});

it('PRV-003: belum pilih lokasi, tidak ada hitungan', function () {
    prvWorkshop();

    Livewire::test(BookingForm::class)
        ->set('booking_type', 'home_service')
        ->call('hitungJarak')
        ->assertSet('distanceText', null)
        ->assertSet('distanceError', null);
});

it('PRV-004: Ke Bengkel tidak menghitung jarak', function () {
    prvWorkshop();

    Livewire::test(BookingForm::class)
        ->set('booking_type', 'workshop')
        ->set('latitude', -7.2800)
        ->set('longitude', 112.7600)
        ->call('hitungJarak')
        ->assertSet('distanceText', null);
});

it('PRV-005: data bengkel belum ada, diam saja tanpa error', function () {
    prvPick()->call('hitungJarak')
        ->assertSet('distanceText', null)
        ->assertSet('distanceError', null);
});