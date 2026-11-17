<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Livewire\BookingForm;
use App\Livewire\BookingStatusCheck;
use App\Models\Service;
use App\Support\Booking\Slots;
use Livewire\Livewire;

// Cek Booking di beranda (aturan 12). Kode modul: CEK.
// Halaman publik: hasil hanya keluar kalau kode booking dan nomor HP cocok,
// pesan gagal sama untuk kode atau nomor salah, harga dan alamat tidak ikut.
// Sejak 17 Nov 2026 cek pakai kode booking + HP (sebelumnya nama + HP).

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

function cekBooking(string $kode, string $hp)
{
    return Livewire::test(BookingStatusCheck::class)
        ->set('booking_code', $kode)
        ->set('phone', $hp)
        ->call('check');
}

/** Kode 6 angka yang pasti bukan milik $s. */
function kodeLain(Service $s): string
{
    return $s->booking_code === '000000' ? '000001' : '000000';
}

test('CEK-001: kode dan nomor cocok, servis tampil (+62 sama dengan 08)', function () {
    $s = servisCek();

    cekBooking($s->booking_code, '+6281234567890')
        ->assertHasNoErrors()
        ->assertSet('results', fn ($r) => count($r) === 1 && $r[0]['status'] === 'pending')
        ->assertSee('Menunggu konfirmasi');
});

test('CEK-002: kode salah, tidak tampil, pesan gagal umum', function () {
    $s = servisCek();

    cekBooking(kodeLain($s), '081234567890')
        ->assertSet('results', [])
        ->assertSee('Booking tidak ditemukan');
});

test('CEK-003: kode benar tapi nomor salah, tidak tampil, pesan gagal sama', function () {
    $s = servisCek();

    cekBooking($s->booking_code, '089999999999')
        ->assertSet('results', [])
        ->assertSee('Booking tidak ditemukan');
});

test('CEK-004: input kosong ditolak validasi', function () {
    cekBooking('', '')
        ->assertHasErrors(['booking_code' => 'required', 'phone' => 'required'])
        ->assertSet('results', null);
});

test('CEK-005: percobaan ke-6 dalam semenit ditolak', function () {
    $s = servisCek();

    foreach (range(1, 5) as $i) {
        cekBooking($s->booking_code, '081234567890')->assertHasNoErrors();
    }

    cekBooking($s->booking_code, '081234567890')
        ->assertHasErrors('phone')
        ->assertSet('results', null);
});

test('CEK-006: servis yang di-soft-delete tidak tampil', function () {
    $s = servisCek();
    $s->delete();

    cekBooking($s->booking_code, '081234567890')->assertSet('results', []);
});

// Sejak D3 (17 Nov 2026) alasan batal boleh tampil (lihat CEK-014), jadi tidak dicek lagi di sini.
test('CEK-007: harga, total, dan alamat tidak ikut tampil', function () {
    $service = servisCek([
        'status' => ServiceStatus::Cancelled,
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.72,
        'longitude' => 113.08,
        'address_note' => 'Rumah pagar hijau RAHASIA',
    ]);
    $service->items()->create(['name' => 'Servis dinamo', 'price' => 123456]);

    cekBooking($service->booking_code, '081234567890')
        ->assertSet('results', fn ($r) => count($r) === 1
            && array_keys($r[0]) === ['booking_code', 'status', 'status_label', 'type', 'scheduled_at', 'complaint', 'cancel_reason'])
        ->assertDontSee('RAHASIA')
        ->assertDontSee('123.456')
        ->assertDontSee('Rp');
});

test('CEK-008: nomor tidak valid tidak mencocokkan servis apa pun', function () {
    $s = servisCek();

    cekBooking($s->booking_code, 'abc')->assertSet('results', []);
});

test('CEK-009: tiap servis baru dapat kode 6 angka yang berbeda', function () {
    $kode = collect(range(1, 20))->map(fn () => servisCek()->booking_code);

    expect($kode->every(fn ($k) => preg_match('/^\d{6}$/', $k) === 1))->toBeTrue()
        ->and($kode->unique())->toHaveCount(20);
});

test('CEK-010: kode diketik dengan spasi tetap cocok', function () {
    $s = servisCek();
    $spasi = substr($s->booking_code, 0, 3) . ' ' . substr($s->booking_code, 3);

    cekBooking($spasi, '081234567890')
        ->assertSet('results', fn ($r) => count($r) === 1);
});

test('CEK-011: nama dan nomor HP saja tanpa kode tidak bisa membuka booking', function () {
    servisCek();

    cekBooking('Budi Santoso', '081234567890')->assertSet('results', []);
});

test('CEK-012: kode booking tidak bisa diisi dari input (bukan fillable)', function () {
    expect((new Service)->isFillable('booking_code'))->toBeFalse();
});

test('CEK-013: setelah booking, kode tampil dan link WA berisi kode', function () {
    // Hari pertama yang masih punya jam kosong.
    $tanggal = collect(range(1, 7))->map(fn ($i) => now()->addDays($i)->startOfDay())
        ->first(fn ($d) => Slots::bookable($d)->contains(fn ($s) => $s['available']));
    $jam = Slots::bookable($tanggal)->first(fn ($s) => $s['available'])['time']->format('Y-m-d H:i');

    $komponen = Livewire::test(BookingForm::class)
        ->set('customer_name', 'Budi Santoso')
        ->set('phone', '081234567890')
        ->set('complaint', 'Dinamo starter mati')
        ->set('slot_date', $tanggal->format('Y-m-d'))
        ->call('pilihSlot', $jam)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $kode = Service::firstOrFail()->booking_code;

    $komponen->assertSet('bookingCode', $kode)
        ->assertSee(substr($kode, 0, 3) . ' ' . substr($kode, 3))
        ->assertSee('Konfirmasi ke WhatsApp')
        ->assertSet('waLink', fn ($link) => str_starts_with($link, 'https://wa.me/628882509745?text=')
            && str_contains(rawurldecode($link), 'Kode booking: ' . $kode));
});

test('CEK-014: booking dibatalkan atau ditolak, alasan dari bengkel tampil', function (ServiceStatus $status) {
    $s = servisCek(['status' => $status, 'cancel_reason' => 'Jadwal montir penuh']);

    cekBooking($s->booking_code, '081234567890')
        ->assertSet('results', fn ($r) => $r[0]['cancel_reason'] === 'Jadwal montir penuh')
        ->assertSee('Alasan dari bengkel')
        ->assertSee('Jadwal montir penuh');
})->with([ServiceStatus::Cancelled, ServiceStatus::Rejected]);

test('CEK-015: booking dengan status lain, alasan tidak tampil', function () {
    $s = servisCek(['status' => ServiceStatus::Confirmed, 'cancel_reason' => 'Sisa catatan lama']);

    cekBooking($s->booking_code, '081234567890')
        ->assertSet('results', fn ($r) => $r[0]['cancel_reason'] === null)
        ->assertDontSee('Sisa catatan lama');
});
