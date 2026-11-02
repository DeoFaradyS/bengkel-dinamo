<?php

use App\Filament\Pages\JarakOngkos;
use App\Models\User;
use App\Models\WorkshopSetting;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    WorkshopSetting::create([
        'latitude' => -7.2575,
        'longitude' => 112.7521,
        'max_distance_km' => 7,
        'fuel_price' => 10000,
    ]);
});

it('SET-001: admin ubah jarak maksimal dan harga BBM, tersimpan', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(['max_distance_km' => 10, 'fuel_price' => 12000])
        ->call('save')
        ->assertHasNoFormErrors();

    $s = WorkshopSetting::first();

    expect((float) $s->max_distance_km)->toBe(10.0)
        ->and($s->fuel_price)->toBe(12000);
});

it('SET-002: jarak maksimal nol ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(['max_distance_km' => 0])
        ->call('save')
        ->assertHasFormErrors(['max_distance_km']);

    expect((float) WorkshopSetting::first()->max_distance_km)->toBe(7.0);
});