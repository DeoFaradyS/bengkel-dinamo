<?php

use App\Filament\Pages\JarakOngkos;
use App\Models\WorkshopSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Satu baris awal, sama seperti hasil seeder.
    WorkshopSetting::create([
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
        'max_distance_km' => 5,
        'price_per_km' => 3000,
    ]);
});

function settingData(array $override = []): array
{
    return array_merge([
        'max_distance_km' => 7.5,
        'price_per_km' => 5000,
        'latitude' => -7.7,
        'longitude' => 113.1,
    ], $override);
}

// ---------- Buka halaman ----------

it('TPG-001: halaman terbuka, nilai awal tampil', function () {
    Livewire::test(JarakOngkos::class)
        ->assertSuccessful()
        ->assertFormSet([
            'max_distance_km' => 5,
            'price_per_km' => 3000,
            'latitude' => -7.7174465,
            'longitude' => 113.0755426,
        ]);
});

// ---------- Simpan ----------

it('TPG-002: data valid disimpan', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData())
        ->call('save')
        ->assertHasNoFormErrors();

    $setting = WorkshopSetting::first();

    expect((float) $setting->max_distance_km)->toBe(7.5)
        ->and((float) $setting->price_per_km)->toBe(5000.0)
        ->and((float) $setting->latitude)->toBe(-7.7)
        ->and((float) $setting->longitude)->toBe(113.1);
});

// ---------- Jarak maksimal ----------

it('TPG-003: jarak maksimal kosong ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['max_distance_km' => null]))
        ->call('save')
        ->assertHasFormErrors(['max_distance_km' => 'required']);
});

it('TPG-004: jarak maksimal 0 ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['max_distance_km' => 0]))
        ->call('save')
        ->assertHasFormErrors(['max_distance_km']);
});

it('TPG-005: jarak maksimal desimal (0,5) diterima', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['max_distance_km' => 0.5]))
        ->call('save')
        ->assertHasNoFormErrors();

    expect((float) WorkshopSetting::first()->max_distance_km)->toBe(0.5);
});

// ---------- Ongkos per km ----------

it('TPG-006: ongkos per km kosong ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['price_per_km' => null]))
        ->call('save')
        ->assertHasFormErrors(['price_per_km' => 'required']);
});

it('TPG-007: ongkos per km 0 diterima', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['price_per_km' => 0]))
        ->call('save')
        ->assertHasNoFormErrors();

    expect((float) WorkshopSetting::first()->price_per_km)->toBe(0.0);
});

it('TPG-008: ongkos per km negatif ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['price_per_km' => -1]))
        ->call('save')
        ->assertHasFormErrors(['price_per_km']);
});

// ---------- Latitude ----------

it('TPG-009: latitude kosong ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['latitude' => null]))
        ->call('save')
        ->assertHasFormErrors(['latitude' => 'required']);
});

it('TPG-010: latitude di luar rentang ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['latitude' => 91]))
        ->call('save')
        ->assertHasFormErrors(['latitude']);
});

// ---------- Longitude ----------

it('TPG-011: longitude kosong ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['longitude' => null]))
        ->call('save')
        ->assertHasFormErrors(['longitude' => 'required']);
});

it('TPG-012: longitude di luar rentang ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['longitude' => 181]))
        ->call('save')
        ->assertHasFormErrors(['longitude']);
});

// ---------- Aturan satu baris ----------

it('TPG-013: simpan berkali-kali, tabel tetap satu baris', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['max_distance_km' => 6]))
        ->call('save')
        ->fillForm(settingData(['max_distance_km' => 8]))
        ->call('save')
        ->assertHasNoFormErrors();

    expect(WorkshopSetting::count())->toBe(1)
        ->and((float) WorkshopSetting::first()->max_distance_km)->toBe(8.0);
});