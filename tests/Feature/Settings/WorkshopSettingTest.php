<?php

use App\Filament\Pages\JarakOngkos;
use App\Models\WorkshopSetting;
use Livewire\Livewire;

beforeEach(function () {
    // Satu baris awal, sama seperti hasil seeder.
    WorkshopSetting::create([
        'phone' => '08882509745',
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
        'maps_url' => 'https://www.google.com/maps?q=-7.7174465,113.0755426',
        'max_distance_km' => 5,
        'fuel_price' => 10000,
    ]);
});

function settingData(array $override = []): array
{
    return array_merge([
        'max_distance_km' => 7.5,
        'fuel_price' => 12000,
        'phone' => '081234567890',
        'maps_url' => 'https://www.google.com/maps?q=-7.7,113.1',
    ], $override);
}

// ---------- Buka halaman ----------

it('TPG-001: halaman terbuka, nilai awal tampil', function () {
    Livewire::test(JarakOngkos::class)
        ->assertSuccessful()
        ->assertFormSet([
            'max_distance_km' => 5,
            'fuel_price' => 10000,
            'phone' => '08882509745',
            'maps_url' => 'https://www.google.com/maps?q=-7.7174465,113.0755426',
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
        ->and((float) $setting->fuel_price)->toBe(12000.0)
        ->and((float) $setting->latitude)->toBe(-7.7)
        ->and((float) $setting->longitude)->toBe(113.1)
        ->and($setting->phone)->toBe('081234567890');
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

// ---------- Harga BBM ----------

it('TPG-006: harga BBM kosong ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['fuel_price' => null]))
        ->call('save')
        ->assertHasFormErrors(['fuel_price' => 'required']);
});

it('TPG-007: harga BBM 0 ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['fuel_price' => 0]))
        ->call('save')
        ->assertHasFormErrors(['fuel_price']);
});

it('TPG-008: harga BBM negatif ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['fuel_price' => -1]))
        ->call('save')
        ->assertHasFormErrors(['fuel_price']);
});

// ---------- Lokasi (link Google Maps) dan nomor bengkel ----------
// Sejak 20 Nov 2026 Latitude/Longitude tidak diketik lagi (aturan 25): TPG-009..012 menguji
// link Google Maps dan nomor HP bengkel. Koordinat dibaca dari link (lihat GMP).

it('TPG-009: link Google Maps kosong ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['maps_url' => null]))
        ->call('save')
        ->assertHasFormErrors(['maps_url' => 'required']);
});

it('TPG-010: link tanpa lokasi ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['maps_url' => 'https://example.com/bukan-peta']))
        ->call('save')
        ->assertHasFormErrors(['maps_url']);

    expect((float) WorkshopSetting::first()->latitude)->toBe(-7.7174465);
});

it('TPG-011: nomor HP bengkel kosong ditolak', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['phone' => null]))
        ->call('save')
        ->assertHasFormErrors(['phone' => 'required']);
});

it('TPG-012: nomor HP bengkel +62 disimpan sebagai 08', function () {
    Livewire::test(JarakOngkos::class)
        ->fillForm(settingData(['phone' => '+62 812-3456-7890']))
        ->call('save')
        ->assertHasNoFormErrors();

    expect(WorkshopSetting::first()->phone)->toBe('081234567890');
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

// ---------- GMP: baca koordinat dari link Google Maps ----------

it('GMP-001: berbagai bentuk link Google Maps terbaca koordinatnya', function (string $link, float $lat, float $lng) {
    expect(\App\Support\GoogleMapsLink::coordinates($link))->toBe(['lat' => $lat, 'lng' => $lng]);
})->with([
    'q=' => ['https://www.google.com/maps?q=-7.7174465,113.0755426', -7.7174465, 113.0755426],
    '@' => ['https://www.google.com/maps/@-7.7174465,113.0755426,17z', -7.7174465, 113.0755426],
    'tempat !3d!4d' => ['https://www.google.com/maps/place/Bengkel/@-7.71,113.07,17z/data=!3m1!4b1!4m6!3m5!1s0x0:0x0!8m2!3d-7.7174465!4d113.0755426', -7.7174465, 113.0755426],
    'koordinat langsung' => ['-7.7174465, 113.0755426', -7.7174465, 113.0755426],
]);

it('GMP-002: link tanpa koordinat atau di luar rentang tidak terbaca', function () {
    expect(\App\Support\GoogleMapsLink::coordinates('https://example.com/abc'))->toBeNull()
        ->and(\App\Support\GoogleMapsLink::coordinates('95, 200'))->toBeNull()
        ->and(\App\Support\GoogleMapsLink::coordinates(''))->toBeNull();
});

it('GMP-003: link pendek maps.app.goo.gl diikuti ke link panjangnya', function () {
    \Illuminate\Support\Facades\Http::fake([
        'maps.app.goo.gl/*' => \Illuminate\Support\Facades\Http::response('', 302, [
            'Location' => 'https://www.google.com/maps/place/Bengkel/data=!3d-7.7174465!4d113.0755426',
        ]),
    ]);

    expect(\App\Support\GoogleMapsLink::coordinates('https://maps.app.goo.gl/AbCdEf123'))
        ->toBe(['lat' => -7.7174465, 'lng' => 113.0755426]);
});

it('GMP-004: nomor bengkel dari pengaturan dipakai di footer dan tombol WhatsApp', function () {
    WorkshopSetting::first()->update(['phone' => '081234567890']);

    $this->get('/')
        ->assertSee('tel:+6281234567890', false)
        ->assertSee('+62 812-3456-7890')
        ->assertSee('wa.me/6281234567890', false);
});
