<?php

use App\Livewire\BookingForm;
use App\Models\Service;
use App\Models\User;
use App\Support\Booking\Slots;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

// Booking untuk customer login (D2). Kode modul: BKA.
// Letakkan di tests/Feature/BookingAccountTest.php.
// tests/Pest.php login satu user acak sebelum tiap tes Feature: tes tamu
// wajib keluar dulu, tes akun memakai actingAs sendiri.

/** Cari satu jam kosong dalam rentang yang bisa dipesan: [tanggal, jam]. */
function slotKosong(): array
{
    $hari = min(7, (int) config('booking.max_days_ahead'));

    for ($i = 1; $i <= $hari; $i++) {
        $tanggal = now()->addDays($i)->startOfDay();
        $slot = Slots::bookable($tanggal)->first(fn ($s) => $s['available']);

        if ($slot !== null) {
            return [$tanggal->format('Y-m-d'), $slot['time']->format('Y-m-d H:i')];
        }
    }

    throw new RuntimeException('Tidak ada jam kosong untuk tes.');
}

function kirimBooking($komponen, array $isi = [])
{
    [$tanggal, $jam] = slotKosong();

    return $komponen
        ->set('customer_name', $isi['customer_name'] ?? $komponen->get('customer_name'))
        ->set('phone', $isi['phone'] ?? $komponen->get('phone'))
        ->set('complaint', 'Dinamo starter bunyi kasar')
        ->set('slot_date', $tanggal)
        ->call('pilihSlot', $jam)
        ->call('submit');
}

function jadiTamu(): void
{
    Auth::logout();
    app('auth')->forgetGuards();
}

test('BKA-001: customer login, form terisi nama dan HP dari akun', function () {
    $user = User::factory()->create(['name' => 'Siti Pelanggan', 'phone' => '081234567890']);

    Livewire::actingAs($user)->test(BookingForm::class)
        ->assertSet('customer_name', 'Siti Pelanggan')
        ->assertSet('phone', '081234567890');
});

test('BKA-002: booking customer login menyimpan user_id, nama, dan HP di services', function () {
    $user = User::factory()->create(['name' => 'Siti Pelanggan', 'phone' => '081234567890']);

    kirimBooking(Livewire::actingAs($user)->test(BookingForm::class))
        ->assertSet('submitted', true);

    $service = Service::firstOrFail();

    expect($service->user_id)->toBe($user->id)
        ->and($service->customer_name)->toBe('Siti Pelanggan')
        ->and($service->customer_phone)->toBe('081234567890');
});

test('BKA-003: HP akun kosong, tersimpan ke akun setelah booking pertama', function () {
    $user = User::factory()->create(['phone' => null]);

    $komponen = Livewire::actingAs($user)->test(BookingForm::class)
        ->assertSet('phone', '');

    kirimBooking($komponen, ['customer_name' => 'Siti', 'phone' => '081298765432'])
        ->assertSet('submitted', true);

    expect($user->fresh()->phone)->toBe('081298765432');
});

test('BKA-004: HP akun sudah ada, tidak ditimpa meski booking pakai HP lain', function () {
    $user = User::factory()->create(['phone' => '081234567890']);

    $komponen = Livewire::actingAs($user)->test(BookingForm::class);

    kirimBooking($komponen, ['phone' => '081298765432'])
        ->assertSet('submitted', true);

    expect($user->fresh()->phone)->toBe('081234567890')
        ->and(Service::firstOrFail()->customer_phone)->toBe('081298765432');
});

test('BKA-005: tamu, form kosong dan user_id NULL', function () {
    jadiTamu();

    $komponen = Livewire::test(BookingForm::class)
        ->assertSet('customer_name', '')
        ->assertSet('phone', '');

    kirimBooking($komponen, ['customer_name' => 'Tamu', 'phone' => '081211112222'])
        ->assertSet('submitted', true);

    expect(Service::firstOrFail()->user_id)->toBeNull();
});

test('BKA-006: customer login boleh mengganti nama (booking untuk orang lain), user_id tetap akun', function () {
    $user = User::factory()->create(['name' => 'Siti Pelanggan', 'phone' => '081234567890']);

    kirimBooking(Livewire::actingAs($user)->test(BookingForm::class), ['customer_name' => 'Pak Haji'])
        ->assertSet('submitted', true);

    $service = Service::firstOrFail();

    expect($service->customer_name)->toBe('Pak Haji')
        ->and($service->user_id)->toBe($user->id);
});

test('BKA-007: booking ditolak validasi, HP akun tidak berubah', function () {
    $user = User::factory()->create(['phone' => null]);

    $komponen = Livewire::actingAs($user)->test(BookingForm::class)
        ->set('customer_name', 'Siti')
        ->set('phone', '081298765432')
        ->call('submit') // tanpa keluhan dan jadwal
        ->assertSet('submitted', false);

    expect($user->fresh()->phone)->toBeNull()
        ->and(Service::count())->toBe(0);
});