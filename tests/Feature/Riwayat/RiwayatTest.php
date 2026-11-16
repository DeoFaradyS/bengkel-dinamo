<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;

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
    jadiTamu();

    $this->get('/riwayat')->assertRedirect(route('login'));
});

test('RWY-002: customer melihat servis miliknya: status, tipe, dan keluhan', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['complaint' => 'Starter bunyi kasar']);

    $service = \App\Models\Service::where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)->get('/riwayat')
        ->assertOk()
        ->assertSee(ServiceStatus::Pending->getLabel())
        ->assertSee(BookingType::Workshop->getLabel());

    $this->actingAs($user)->get('/riwayat/' . $service->id)
        ->assertOk()
        ->assertSee('Starter bunyi kasar');
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
    $lama = now()->addDays(1)->startOfMinute();
    $baru = now()->addDays(5)->startOfMinute();

    servisRiwayat($user, ['complaint' => 'Keluhan lama AAA', 'scheduled_at' => $lama]);
    servisRiwayat($user, ['complaint' => 'Keluhan baru BBB', 'scheduled_at' => $baru]);

    $this->actingAs($user)->get('/riwayat')
        ->assertSeeInOrder([$baru->format('d/m/Y H:i'), $lama->format('d/m/Y H:i')]);
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

    jadiTamu();

    $this->get('/')->assertDontSee('href="/riwayat"', false);
});

test('RWY-010: filter status hanya memuat status itu', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['status' => ServiceStatus::Pending]);
    servisRiwayat($user, ['status' => ServiceStatus::Rejected]);

    // Label status juga ada di dropdown filter, jadi cek lewat data view, bukan teks.
    $this->actingAs($user)->get('/riwayat?status=rejected')
        ->assertOk()
        ->assertViewHas('services', fn ($p) => $p->total() === 1
            && $p->first()->status === ServiceStatus::Rejected);
});

test('RWY-011: detail menampilkan keluhan booking sendiri', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['complaint' => 'Dinamo berisik DETAIL']);
    $service = \App\Models\Service::where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)->get('/riwayat/'.$service->id)
        ->assertOk()
        ->assertSee('Dinamo berisik DETAIL');
});

test('RWY-012: detail booking milik orang lain 404', function () {
    $pemilik = User::factory()->create();
    servisRiwayat($pemilik, []);
    $service = \App\Models\Service::where('user_id', $pemilik->id)->firstOrFail();

    $this->actingAs(User::factory()->create())->get('/riwayat/'.$service->id)->assertNotFound();
});

test('RWY-013: alasan batal tidak tampil di detail', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['status' => ServiceStatus::Cancelled, 'cancel_reason' => 'Catatan internal XYZ']);
    $service = \App\Models\Service::where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)->get('/riwayat/'.$service->id)
        ->assertOk()
        ->assertDontSee('Catatan internal XYZ');
});

test('RWY-014: owner terlempar ke panel dari riwayat dan detail', function () {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)->get('/riwayat')->assertRedirect('/admin');
    $this->actingAs($owner)->get('/riwayat/1')->assertRedirect('/admin');
});

test('RWY-015: tamu diarahkan ke halaman masuk dari detail dan invoice', function () {
    jadiTamu();

    $this->get('/riwayat/1')->assertRedirect('/masuk');
    $this->get('/riwayat/1/invoice')->assertRedirect('/masuk');
});

test('RWY-016: invoice booking yang belum selesai 404', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['status' => ServiceStatus::Confirmed]);
    $service = \App\Models\Service::where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)->get('/riwayat/'.$service->id.'/invoice')->assertNotFound();
});

test('RWY-017: invoice booking selesai milik sendiri berupa PDF', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['status' => ServiceStatus::Completed]);
    $service = \App\Models\Service::where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)->get('/riwayat/'.$service->id.'/invoice')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('RWY-018: invoice booking selesai milik orang lain 404', function () {
    $pemilik = User::factory()->create();
    servisRiwayat($pemilik, ['status' => ServiceStatus::Completed]);
    $service = \App\Models\Service::where('user_id', $pemilik->id)->firstOrFail();

    $this->actingAs(User::factory()->create())->get('/riwayat/'.$service->id.'/invoice')->assertNotFound();
});
test('RWY-019: filter tipe hanya memuat tipe itu', function () {
    $user = User::factory()->create();
    servisRiwayat($user, ['booking_type' => BookingType::Workshop]);
    servisRiwayat($user, ['booking_type' => BookingType::HomeService, 'latitude' => -7.72, 'longitude' => 113.08]);

    $this->actingAs($user)->get('/riwayat?tipe=' . BookingType::HomeService->value)
        ->assertOk()
        ->assertViewHas('services', fn ($p) => $p->total() === 1
            && $p->first()->booking_type === BookingType::HomeService);
});

test('RWY-020: filter pembayaran memisahkan Lunas dan Belum Lunas, hanya servis Selesai', function () {
    $user = User::factory()->create();
    $lunas = servisRiwayat($user, ['status' => ServiceStatus::Completed]);
    $lunas->forceFill(['paid_at' => now()])->save();
    $belum = servisRiwayat($user, ['status' => ServiceStatus::Completed]);
    servisRiwayat($user, ['status' => ServiceStatus::Pending]);

    $this->actingAs($user)->get('/riwayat?bayar=lunas')
        ->assertViewHas('services', fn ($p) => $p->total() === 1 && $p->first()->is($lunas));

    $this->actingAs($user)->get('/riwayat?bayar=belum_lunas')
        ->assertViewHas('services', fn ($p) => $p->total() === 1 && $p->first()->is($belum));
});

test('RWY-021: urut Total menghitung jasa + part + ongkos transport', function () {
    \App\Models\WorkshopSetting::create([
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
        'max_distance_km' => 20,
        'fuel_price' => 10000,
    ]);
    $user = User::factory()->create();
    // Ongkos saja: 10 km PP = Rp5.000.
    $ongkos = servisRiwayat($user, [
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.72,
        'longitude' => 113.08,
        'distance_km' => 10,
    ]);
    $jasa = servisRiwayat($user);
    $jasa->items()->create(['name' => 'Cek', 'price' => 3000]);

    $this->actingAs($user)->get('/riwayat?sort=total&arah=desc')
        ->assertViewHas('services', fn ($p) => $p->pluck('id')->all() === [$ongkos->id, $jasa->id]);

    $this->actingAs($user)->get('/riwayat?sort=total&arah=asc')
        ->assertViewHas('services', fn ($p) => $p->pluck('id')->all() === [$jasa->id, $ongkos->id]);
});

test('RWY-022: per halaman 25 dan 50 dipakai, nilai lain jatuh ke 10', function (string $nilai, int $harapan) {
    $user = User::factory()->create();
    foreach (range(1, 51) as $i) {
        servisRiwayat($user, ['scheduled_at' => now()->addDays($i)]);
    }

    $this->actingAs($user)->get('/riwayat?per_page=' . $nilai)
        ->assertViewHas('services', fn ($p) => $p->perPage() === $harapan && $p->count() === $harapan);
})->with([
    '25' => ['25', 25],
    '50' => ['50', 50],
    '100 ditolak' => ['100', 10],
    'teks ditolak' => ['abc', 10],
]);
