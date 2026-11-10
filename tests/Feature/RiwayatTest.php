<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Riwayat Saya (/riwayat). Kode modul: RWY.
// Letakkan di tests/Feature/RiwayatTest.php.
// tests/Pest.php login satu user acak sebelum tiap tes Feature: tes
// tamu keluar dulu, tes akun memakai actingAs sendiri.

function servisRiwayat(?User $user, array $o = []): Service
{
    return Service::create(array_merge([
        'customer_name' => 'Pelanggan',
        'customer_phone' => '081200000000',
        'complaint' => 'Keluhan biasa',
        'scheduled_at' => now()->addDay(),
        'status' => ServiceStatus::Pending,
        'booking_type' => BookingType::Workshop,
        'user_id' => $user?->id,
    ], $o));
}

test('RWY-001: tamu diarahkan ke halaman masuk', function () {
    Auth::logout();
    app('auth')->forgetGuards();

    $this->get('/riwayat')->assertRedirect(route('login'));
});

test('RWY-002: customer melihat servis miliknya: status, tipe, dan keluhan', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['complaint' => 'Starter bunyi kasar']);

    $this->actingAs($user)->get('/riwayat')
        ->assertOk()
        ->assertSee('Starter bunyi kasar')
        ->assertSee(ServiceStatus::Pending->getLabel())
        ->assertSee(BookingType::Workshop->getLabel());
});

test('RWY-003: servis milik user lain dan servis tamu tidak tampil', function () {
    $user = User::factory()->create();
    servisRiwayat(User::factory()->create(), ['complaint' => 'Milik orang lain']);
    servisRiwayat(null, ['complaint' => 'Milik tamu']);

    $this->actingAs($user)->get('/riwayat')
        ->assertOk()
        ->assertDontSee('Milik orang lain')
        ->assertDontSee('Milik tamu');
});

test('RWY-004: alamat, alasan batal, dan harga tidak tampil', function () {
    $user = User::factory()->create();
    $service = servisRiwayat($user, ['address_note' => 'Jalan Rahasia 99']);
    $service->forceFill(['cancel_reason' => 'Catatan internal pemilik'])->save();

    $this->actingAs($user)->get('/riwayat')
        ->assertOk()
        ->assertDontSee('Jalan Rahasia 99')
        ->assertDontSee('Catatan internal pemilik')
        ->assertDontSeeText('Rp'); // teks terlihat saja: token CSRF acak bisa memuat 'Rp'
});

test('RWY-005: servis yang di-soft-delete tidak tampil', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['complaint' => 'Sudah dihapus'])->delete();

    $this->actingAs($user)->get('/riwayat')
        ->assertOk()
        ->assertDontSee('Sudah dihapus');
});

test('RWY-006: urutan terbaru dulu berdasarkan jadwal', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['complaint' => 'Keluhan lama AAA', 'scheduled_at' => now()->addDays(1)]);
    servisRiwayat($user, ['complaint' => 'Keluhan baru BBB', 'scheduled_at' => now()->addDays(5)]);

    $this->actingAs($user)->get('/riwayat')
        ->assertSeeInOrder(['Keluhan baru BBB', 'Keluhan lama AAA']);
});

test('RWY-007: paginasi 10 per halaman', function () {
    $user = User::factory()->create();
    foreach (range(1, 11) as $i) {
        servisRiwayat($user, ['scheduled_at' => now()->addDays($i)]);
    }

    $services = $this->actingAs($user)->get('/riwayat')->assertOk()->viewData('services');

    expect($services->count())->toBe(10)
        ->and($services->total())->toBe(11);
});

test('RWY-008: customer tanpa booking melihat pesan kosong', function () {
    $this->actingAs(User::factory()->create())->get('/riwayat')
        ->assertOk()
        ->assertSee('Belum ada booking');
});

test('RWY-009: navbar customer memuat tautan Riwayat Saya, tamu tidak', function () {
    $this->actingAs(User::factory()->create())->get('/')
        ->assertSee('href="/riwayat"', false);

    Auth::logout();
    app('auth')->forgetGuards();

    $this->get('/')->assertDontSee('href="/riwayat"', false);
});