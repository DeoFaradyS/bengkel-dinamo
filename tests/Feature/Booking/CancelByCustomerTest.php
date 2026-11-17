<?php

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Livewire\BookingStatusCheck;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

// Pelanggan membatalkan booking sendiri (17 Nov 2026). Kode modul: BTL.
// Hanya selama Menunggu, lewat Cek Booking (kode + HP) atau detail Riwayat (pemilik akun).
// Setelah Dikonfirmasi, pembatalan lewat bengkel. Ganti jadwal = batalkan lalu booking ulang.

function servisBatal(array $o = []): Service
{
    return Service::create(array_merge([
        'customer_name' => 'Bu Sari',
        'customer_phone' => '081234500088',
        'complaint' => 'Dinamo panas',
        'scheduled_at' => now()->addDay(),
        'status' => ServiceStatus::Pending,
        'booking_type' => BookingType::Workshop,
    ], $o));
}

function cekLaluBatal(Service $s, string $hp, ?string $alasan = null)
{
    return Livewire::test(BookingStatusCheck::class)
        ->set('booking_code', $s->booking_code)
        ->set('phone', $hp)
        ->call('check')
        ->call('batalkan', $s->booking_code, $alasan);
}

test('BTL-001: Cek Booking, booking Menunggu dibatalkan dengan alasan', function () {
    jadiTamu();
    $s = servisBatal();

    cekLaluBatal($s, '081234500088', 'Ganti jadwal')
        ->assertSet('results', fn ($r) => $r[0]['status'] === 'cancelled')
        ->assertSee('Buat booking baru');

    expect($s->fresh()->status)->toBe(ServiceStatus::Cancelled)
        ->and($s->fresh()->cancel_reason)->toBe('Dibatalkan pelanggan: Ganti jadwal');
});

test('BTL-002: tanpa alasan atau alasan di luar daftar, tersimpan "Dibatalkan pelanggan"', function (?string $alasan) {
    jadiTamu();
    $s = servisBatal();

    cekLaluBatal($s, '081234500088', $alasan);

    expect($s->fresh()->cancel_reason)->toBe('Dibatalkan pelanggan');
})->with([
    'kosong' => null,
    'di luar daftar' => '<script>alert(1)</script>',
]);

test('BTL-003: Cek Booking, booking Dikonfirmasi tidak bisa dibatalkan, yang tampil link WA', function () {
    jadiTamu();
    $s = servisBatal(['status' => ServiceStatus::Confirmed]);

    Livewire::test(BookingStatusCheck::class)
        ->set('booking_code', $s->booking_code)
        ->set('phone', '081234500088')
        ->call('check')
        ->assertDontSee('Batalkan booking')
        ->assertSee('Hubungi bengkel lewat WhatsApp')
        ->call('batalkan', $s->booking_code, 'Ganti jadwal');

    expect($s->fresh()->status)->toBe(ServiceStatus::Confirmed);
});

test('BTL-004: Cek Booking, kode benar tapi nomor HP salah, tidak bisa membatalkan', function () {
    jadiTamu();
    $s = servisBatal();

    cekLaluBatal($s, '089999999999');

    expect($s->fresh()->status)->toBe(ServiceStatus::Pending);
});

test('BTL-005: membatalkan ikut batas 5 percobaan per menit', function () {
    jadiTamu();
    $s = servisBatal();

    $k = Livewire::test(BookingStatusCheck::class)
        ->set('booking_code', $s->booking_code)
        ->set('phone', '089999999999');

    foreach (range(1, 5) as $i) {
        $k->call('batalkan', $s->booking_code);
    }

    $k->set('phone', '081234500088')
        ->call('batalkan', $s->booking_code)
        ->assertHasErrors('phone');

    expect($s->fresh()->status)->toBe(ServiceStatus::Pending);
});

test('BTL-006: Riwayat, pemilik membatalkan booking Menunggu', function () {
    $user = User::factory()->create();
    $s = servisBatal(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get('/riwayat/' . $s->id)
        ->assertSee('Batalkan booking');

    $this->actingAs($user)
        ->post('/riwayat/' . $s->id . '/batal', ['alasan' => 'Tidak jadi servis'])
        ->assertRedirect('/riwayat/' . $s->id);

    expect($s->fresh()->status)->toBe(ServiceStatus::Cancelled)
        ->and($s->fresh()->cancel_reason)->toBe('Dibatalkan pelanggan: Tidak jadi servis');
});

test('BTL-007: Riwayat, booking milik orang lain 404 dan tidak berubah', function () {
    $pemilik = User::factory()->create();
    $s = servisBatal(['user_id' => $pemilik->id]);

    $this->actingAs(User::factory()->create())
        ->post('/riwayat/' . $s->id . '/batal')
        ->assertNotFound();

    expect($s->fresh()->status)->toBe(ServiceStatus::Pending);
});

test('BTL-008: Riwayat, booking Dikonfirmasi tidak bisa dibatalkan', function () {
    $user = User::factory()->create();
    $s = servisBatal(['user_id' => $user->id, 'status' => ServiceStatus::Confirmed]);

    $this->actingAs($user)
        ->get('/riwayat/' . $s->id)
        ->assertDontSee('Batalkan booking');

    $this->actingAs($user)->post('/riwayat/' . $s->id . '/batal');

    expect($s->fresh()->status)->toBe(ServiceStatus::Confirmed);
});

test('BTL-009: owner dapat notifikasi saat pelanggan membatalkan', function () {
    $owner = User::factory()->owner()->create();
    jadiTamu();
    $s = servisBatal();

    cekLaluBatal($s, '081234500088', 'Ganti jadwal');

    expect($owner->notifications()->count())->toBe(1)
        ->and($owner->notifications()->first()->data['body'])->toContain('Ganti jadwal');
});

test('BTL-010: setelah dibatalkan, nomor yang sama bisa booking lagi', function () {
    jadiTamu();
    $s = servisBatal(['customer_phone' => '081234500088']);

    cekLaluBatal($s, '081234500088');

    app(CreateBooking::class)->handle([
        'customer_name' => 'Bu Sari',
        'phone' => '081234500088',
        'complaint' => 'Dinamo panas',
        'scheduled_at' => nextSlot(2)->format('Y-m-d H:i'),
        'booking_type' => 'workshop',
    ]);

    expect(Service::where('customer_phone', '081234500088')->count())->toBe(2);
});
