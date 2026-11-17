<?php

use App\Enums\ServiceStatus;
use App\Livewire\BookingForm;
use App\Models\Service;
use Livewire\Livewire;
use Illuminate\Support\Carbon;

// Halaman publik: tes ini jalan sebagai tamu, bukan admin.
beforeEach(fn() => auth()->logout());

function fillBooking($component, array $override = [])
{
    $data = array_merge([
        'customer_name' => 'Bu Sari',
        'phone' => '081234500001',
        'complaint' => 'Dinamo panas',
        'scheduled_at' => nextSlot()->format('Y-m-d H:i'),
    ], $override);

    foreach ($data as $key => $value) {
        $component->set($key, $value);
    }

    return $component;
}

it('BKG-010: halaman booking terbuka tanpa login', function () {
    $this->get('/')->assertOk();
});

it('BKG-011: kirim booking valid, tersimpan Menunggu', function () {
    fillBooking(Livewire::test(BookingForm::class), ['phone' => '+62 812 3450 0001'])
        ->call('submit')
        ->assertHasNoErrors();

    $service = Service::first();

    expect($service->status)->toBe(ServiceStatus::Pending)
        ->and($service->customer_phone)->toBe('081234500001');
});

it('BKG-012: field wajib kosong ditolak', function (string $field) {
    fillBooking(Livewire::test(BookingForm::class), [$field => ''])
        ->call('submit')
        ->assertHasErrors([$field => 'required']);

    expect(Service::count())->toBe(0);
})->with(['customer_name', 'phone', 'complaint', 'scheduled_at']);

it('BKG-013: HP aktif ditolak, data orang lain tidak bocor', function () {
    Service::factory()->create(['customer_phone' => '081234500001', 'customer_name' => 'Rahasia Orang']);

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
        fillBooking(Livewire::test(BookingForm::class), [
            'phone' => '08123450010' . $i,
            'scheduled_at' => nextSlot($i),
        ])
            ->call('submit')
            ->assertHasNoErrors();
    }

    fillBooking(Livewire::test(BookingForm::class), [
        'phone' => '081234500199',
        'scheduled_at' => nextSlot(6),
    ])
        ->call('submit')
        ->assertHasErrors(['throttle']);

    expect(Service::count())->toBe(5);
});

it('BKG-016: memilih slot mengisi jadwal', function () {
    $this->travelTo(Carbon::parse('2026-10-23 06:00'));

    Livewire::test(BookingForm::class)
        ->set('slot_date', '2026-10-23')
        ->call('pilihSlot', '2026-10-23 09:00')
        ->assertSet('scheduled_at', '2026-10-23 09:00');
});

it('BKG-017: slot penuh tidak bisa dipilih', function () {
    $this->travelTo(Carbon::parse('2026-10-23 06:00'));
    Service::factory()->create([
        'scheduled_at' => Carbon::parse('2026-10-23 09:00'),
        'status' => ServiceStatus::Pending,
    ]);

    Livewire::test(BookingForm::class)
        ->set('slot_date', '2026-10-23')
        ->call('pilihSlot', '2026-10-23 09:00')
        ->assertSet('scheduled_at', '');
});

it('BKG-018: tanggal kemarin dan terlalu jauh dikoreksi ke batas', function () {
    config(['booking.max_days_ahead' => 14]);
    $this->travelTo(Carbon::parse('2026-10-23 06:00'));

    Livewire::test(BookingForm::class)
        ->set('slot_date', '2026-10-22')
        ->assertSet('slot_date', '2026-10-23')
        ->set('slot_date', '2026-12-31')
        ->assertSet('slot_date', '2026-11-06');
});
it('BKG-019: HP masih punya booking aktif, pesan jelas dengan link ke Cek Booking', function () {
    Service::factory()->create(['customer_phone' => '081234500001', 'status' => ServiceStatus::Pending]);

    fillBooking(Livewire::test(BookingForm::class))
        ->call('submit')
        ->assertHasErrors(['phone'])
        ->assertSee('Nomor ini masih punya booking yang belum selesai.')
        ->assertSee('/#cek-booking', false);
});

it('BKG-020: Buat booking lain membuka form kosong, nomor yang sama tetap ditolak', function () {
    $komponen = fillBooking(Livewire::test(BookingForm::class))
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true)
        ->call('bookingBaru')
        ->assertSet('submitted', false)
        ->assertSet('bookingCode', null)
        ->assertSet('phone', '');

    fillBooking($komponen, ['scheduled_at' => nextSlot(2)->format('Y-m-d H:i')])
        ->call('submit')
        ->assertHasErrors(['phone']);

    expect(Service::count())->toBe(1);
});
