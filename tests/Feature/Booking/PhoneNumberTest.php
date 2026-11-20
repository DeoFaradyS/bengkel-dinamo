<?php

use App\Filament\Pages\JarakOngkos;
use App\Livewire\BookingForm;
use App\Livewire\BookingStatusCheck;
use App\Models\Service;
use App\Models\User;
use App\Support\Booking\Slots;
use App\Support\PhoneNumber;
use Livewire\Livewire;

// Nomor HP: semua bentuk penulisan dirapikan jadi 08..., lalu dicek 08 + 8..12 angka
// (21 Nov 2026, aturan 35). Kode modul: HPN.

function hpnBooking(string $hp)
{
    $tanggal = collect(range(1, 7))->map(fn ($i) => now()->addDays($i)->startOfDay())
        ->first(fn ($d) => Slots::bookable($d)->contains(fn ($s) => $s['available']));
    $jam = Slots::bookable($tanggal)->first(fn ($s) => $s['available'])['time']->format('Y-m-d H:i');

    return Livewire::test(BookingForm::class)
        ->set('customer_name', 'Budi Santoso')
        ->set('phone', $hp)
        ->set('complaint', 'Dinamo starter mati')
        ->set('slot_date', $tanggal->format('Y-m-d'))
        ->call('pilihSlot', $jam)
        ->call('submit');
}

test('HPN-001: +62, 62, 08, dan 8 (tanpa 0) dengan spasi atau strip dirapikan jadi bentuk yang sama', function (string $ketik) {
    expect(PhoneNumber::normalize($ketik))->toBe('081234567890')
        ->and(PhoneNumber::isValid($ketik))->toBeTrue();
})->with([
    '+62 812-3456-7890',
    '62812 3456 7890',
    '0812.3456.7890',
    '812 3456 7890',
    '(0812) 3456-7890',
]);

test('HPN-002: batas panjang, 10 dan 14 digit sah, 9 dan 15 digit atau bukan 08 ditolak', function () {
    expect(PhoneNumber::isValid('0812345678'))->toBeTrue()          // 10 digit
        ->and(PhoneNumber::isValid('08123456789012'))->toBeTrue()   // 14 digit
        ->and(PhoneNumber::isValid('081234567'))->toBeFalse()       // 9 digit
        ->and(PhoneNumber::isValid('081234567890123'))->toBeFalse() // 15 digit
        ->and(PhoneNumber::isValid('0212345678'))->toBeFalse()      // telepon rumah
        ->and(PhoneNumber::isValid('abc'))->toBeFalse();
});

test('HPN-003: booking dengan nomor tanpa 0 tersimpan sebagai 08..., nomor ngawur ditolak', function () {
    jadiTamu();

    hpnBooking('812 3456 7890')->assertHasNoErrors()->assertSet('submitted', true);

    expect(Service::firstOrFail()->customer_phone)->toBe('081234567890');

    hpnBooking('12345')->assertHasErrors(['phone' => 'regex']);

    expect(Service::count())->toBe(1);
});

test('HPN-004: Cek Booking cocok walau nomor diketik dengan +62 atau tanpa 0', function () {
    jadiTamu();
    hpnBooking('081234567890');
    $kode = Service::firstOrFail()->booking_code;

    foreach (['+62 812-3456-7890', '812 3456 7890'] as $ketik) {
        Livewire::test(BookingStatusCheck::class)
            ->set('booking_code', $kode)
            ->set('phone', $ketik)
            ->call('check')
            ->assertHasNoErrors()
            ->assertSet('results', fn ($r) => count($r) === 1);
    }
});

test('HPN-005: nomor bengkel di Jarak & Ongkos ditolak kalau tidak sah', function () {
    $this->actingAs(User::factory()->owner()->create());
    \App\Models\WorkshopSetting::create([
        'phone' => '08882509745', 'latitude' => -7.71, 'longitude' => 113.07,
        'maps_url' => 'https://www.google.com/maps?q=-7.71,113.07', 'max_distance_km' => 5, 'fuel_price' => 10000,
    ]);

    Livewire::test(JarakOngkos::class)
        ->fillForm(['phone' => '021-123'])
        ->call('save')
        ->assertHasFormErrors(['phone']);

    Livewire::test(JarakOngkos::class)
        ->fillForm(['phone' => '+62 888-2509-745'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(\App\Models\WorkshopSetting::first()->phone)->toBe('08882509745');
});
