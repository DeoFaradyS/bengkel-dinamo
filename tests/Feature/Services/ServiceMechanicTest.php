<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\EditService;
use App\Livewire\BookingStatusCheck;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

// Montir (20 Nov 2026). Kode modul: MTR.
// Teks bebas di services.mechanic_names, diisi mulai Dikonfirmasi (tidak wajib),
// catatan internal: tidak tampil ke pelanggan.

// Awalnya (20 Nov 2026) hanya saat Dikerjakan, diubah di hari yang sama: mulai Dikonfirmasi.
test('MTR-001: isian Montir tersembunyi saat Menunggu, muncul mulai Dikonfirmasi', function () {
    $menunggu = Service::factory()->status(ServiceStatus::Pending)->create();

    Livewire::test(EditService::class, ['record' => $menunggu->getRouteKey()])
        ->assertFormFieldHidden('mechanic_names');

    foreach ([ServiceStatus::Confirmed, ServiceStatus::InProgress] as $status) {
        $s = Service::factory()->status($status)->create();

        Livewire::test(EditService::class, ['record' => $s->getRouteKey()])
            ->assertFormFieldVisible('mechanic_names');
    }
});

test('MTR-002: nama montir tersimpan dari form ubah', function () {
    $s = Service::factory()->status(ServiceStatus::InProgress)->create();

    Livewire::test(EditService::class, ['record' => $s->getRouteKey()])
        ->fillForm(['mechanic_names' => 'Budi, Andi'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($s->fresh()->mechanic_names)->toBe('Budi, Andi');
});

test('MTR-003: nama montir lebih dari 255 karakter ditolak', function () {
    $s = Service::factory()->status(ServiceStatus::InProgress)->create();

    Livewire::test(EditService::class, ['record' => $s->getRouteKey()])
        ->fillForm(['mechanic_names' => str_repeat('a', 256)])
        ->call('save')
        ->assertHasFormErrors(['mechanic_names' => 'max']);
});

test('MTR-004: nama montir tidak tampil di Cek Booking dan detail Riwayat', function () {
    $user = User::factory()->create();
    $s = Service::factory()->status(ServiceStatus::InProgress)->create([
        'user_id' => $user->id,
        'customer_phone' => '081234500099',
        'mechanic_names' => 'Montir Rahasia',
    ]);

    $this->actingAs($user)->get('/riwayat/' . $s->id)
        ->assertOk()
        ->assertDontSee('Montir Rahasia');

    jadiTamu();

    Livewire::test(BookingStatusCheck::class)
        ->set('booking_code', $s->booking_code)
        ->set('phone', '081234500099')
        ->call('check')
        ->assertSet('results', fn ($r) => count($r) === 1)
        ->assertDontSee('Montir Rahasia');
});
