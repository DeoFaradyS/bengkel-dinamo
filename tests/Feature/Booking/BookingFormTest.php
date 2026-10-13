<?php

use App\Enums\ServiceStatus;
use App\Livewire\BookingForm;
use App\Models\Service;
use Livewire\Livewire;

// Halaman publik: tes ini jalan sebagai tamu, bukan admin.
beforeEach(fn () => auth()->logout());

function fillBooking($component, array $override = [])
{
    $data = array_merge([
        'customer_name' => 'Bu Sari',
        'phone' => '081234500001',
        'complaint' => 'Dinamo panas',
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i'),
    ], $override);

    foreach ($data as $key => $value) {
        $component->set($key, $value);
    }

    return $component;
}

it('BKG-010: halaman booking terbuka tanpa login', function () {
    $this->get('/booking')->assertOk();
});

it('BKG-011: kirim booking valid, tersimpan Menunggu Konfirmasi', function () {
    fillBooking(Livewire::test(BookingForm::class), ['phone' => '+62 812 3450 0001'])
        ->call('submit')
        ->assertHasNoErrors();

    $service = Service::first();

    expect($service->status)->toBe(ServiceStatus::Pending)
        ->and($service->phone)->toBe('081234500001');
});

it('BKG-012: field wajib kosong ditolak', function (string $field) {
    fillBooking(Livewire::test(BookingForm::class), [$field => ''])
        ->call('submit')
        ->assertHasErrors([$field => 'required']);

    expect(Service::count())->toBe(0);
})->with(['customer_name', 'phone', 'complaint', 'scheduled_at']);

it('BKG-013: HP aktif ditolak, data orang lain tidak bocor', function () {
    Service::factory()->create(['phone' => '081234500001', 'customer_name' => 'Rahasia Orang']);

    fillBooking(Livewire::test(BookingForm::class))
        ->call('submit')
        ->assertHasErrors(['phone'])
        ->assertDontSee('Rahasia Orang');

    expect(Service::count())->toBe(1);
});

it('BKG-014: honeypot terisi, booking tidak disimpan', function () {
    fillBooking(Livewire::test(BookingForm::class))
        ->set('website', 'http://spam.example')
        ->call('submit');

    expect(Service::count())->toBe(0);
});

it('BKG-015: lebih dari 5 kiriman per menit ditolak', function () {
    foreach (range(1, 5) as $i) {
        fillBooking(Livewire::test(BookingForm::class), ['phone' => '08123450010'.$i])
            ->call('submit')
            ->assertHasNoErrors();
    }

    fillBooking(Livewire::test(BookingForm::class), ['phone' => '081234500199'])
        ->call('submit')
        ->assertHasErrors(['throttle']);

    expect(Service::count())->toBe(5);
});