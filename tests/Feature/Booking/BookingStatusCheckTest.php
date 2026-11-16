<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Livewire\BookingStatusCheck;
use App\Models\Service;
use Livewire\Livewire;

// Cek Booking di beranda (aturan 12). Kode modul: CEK.
// Halaman publik: hasil hanya keluar kalau nama dan nomor HP cocok,
// pesan gagal sama untuk nama atau nomor salah, harga dan alamat tidak ikut.

beforeEach(function () {
    jadiTamu();
});

function servisCek(array $o = []): Service
{
    return Service::create(array_merge([
        'customer_name' => 'Budi Santoso',
        'customer_phone' => '081234567890',
        'complaint' => 'Dinamo starter mati',
        'scheduled_at' => now()->addDay(),
        'status' => ServiceStatus::Pending,
        'booking_type' => BookingType::Workshop,
    ], $o));
}

function cekBooking(string $nama, string $hp)
{
    return Livewire::test(BookingStatusCheck::class)
        ->set('customer_name', $nama)
        ->set('phone', $hp)
        ->call('check');
}

test('CEK-001: nama dan nomor cocok, servis tampil (nama tidak peka huruf besar, +62 sama dengan 08)', function () {
    servisCek();

    cekBooking('  budi SANTOSO ', '+6281234567890')
        ->assertHasNoErrors()
        ->assertSet('results', fn ($r) => count($r) === 1 && $r[0]['status'] === 'pending')
        ->assertSee('Menunggu konfirmasi');
});

test('CEK-002: nama salah, tidak tampil, pesan gagal umum', function () {
    servisCek();

    cekBooking('Andi', '081234567890')
        ->assertSet('results', [])
        ->assertSee('Booking tidak ditemukan');
});

test('CEK-003: nomor salah, tidak tampil, pesan gagal sama dengan nama salah', function () {
    servisCek();

    cekBooking('Budi Santoso', '089999999999')
        ->assertSet('results', [])
        ->assertSee('Booking tidak ditemukan');
});

test('CEK-004: input kosong ditolak validasi', function () {
    cekBooking('', '')
        ->assertHasErrors(['customer_name' => 'required', 'phone' => 'required'])
        ->assertSet('results', null);
});

test('CEK-005: percobaan ke-6 dalam semenit ditolak', function () {
    servisCek();

    foreach (range(1, 5) as $i) {
        cekBooking('Budi Santoso', '081234567890')->assertHasNoErrors();
    }

    cekBooking('Budi Santoso', '081234567890')
        ->assertHasErrors('phone')
        ->assertSet('results', null);
});

test('CEK-006: servis yang di-soft-delete tidak tampil', function () {
    servisCek()->delete();

    cekBooking('Budi Santoso', '081234567890')->assertSet('results', []);
});

test('CEK-007: harga, total, alamat, dan alasan batal tidak ikut tampil', function () {
    $service = servisCek([
        'status' => ServiceStatus::Cancelled,
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.72,
        'longitude' => 113.08,
        'address_note' => 'Rumah pagar hijau RAHASIA',
        'cancel_reason' => 'Catatan internal RAHASIA',
    ]);
    $service->items()->create(['name' => 'Servis dinamo', 'price' => 123456]);

    cekBooking('Budi Santoso', '081234567890')
        ->assertSet('results', fn ($r) => count($r) === 1
            && array_keys($r[0]) === ['status', 'status_label', 'type', 'scheduled_at', 'complaint'])
        ->assertDontSee('RAHASIA')
        ->assertDontSee('123.456')
        ->assertDontSee('Rp');
});

test('CEK-008: nomor tidak valid tidak mencocokkan servis apa pun', function () {
    servisCek();

    cekBooking('Budi Santoso', 'abc')->assertSet('results', []);
});
