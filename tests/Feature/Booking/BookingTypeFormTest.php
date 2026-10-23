<?php

use App\Enums\BookingType;
use App\Livewire\BookingForm;
use App\Models\Service;
use Livewire\Livewire;

// Halaman publik: tes ini jalan sebagai tamu, bukan admin.
beforeEach(fn () => auth()->logout());

it('TIP-001: halaman booking menampilkan dua pilihan tipe', function () {
    $this->get('/booking')
        ->assertOk()
        ->assertSee('Ke Bengkel')
        ->assertSee('Home Service');
});

it('TIP-002: default workshop, kotak lokasi tersembunyi', function () {
    Livewire::test(BookingForm::class)
        ->assertSet('booking_type', BookingType::Workshop->value)
        ->assertDontSee('Lokasi montir datang');
});

it('TIP-003: pilih home service, kotak lokasi muncul', function () {
    Livewire::test(BookingForm::class)
        ->set('booking_type', BookingType::HomeService->value)
        ->assertSee('Lokasi montir datang');
});

it('TIP-004: balik ke workshop, kotak lokasi hilang lagi', function () {
    Livewire::test(BookingForm::class)
        ->set('booking_type', BookingType::HomeService->value)
        ->set('booking_type', BookingType::Workshop->value)
        ->assertDontSee('Lokasi montir datang');
});

it('TIP-005: home service tanpa lokasi, pesan error tampil di layar', function () {
    Livewire::test(BookingForm::class)
        ->set('customer_name', 'Pak Budi')
        ->set('phone', '081234500003')
        ->set('complaint', 'Dinamo starter mati')
        ->set('scheduled_at', nextSlot()->format('Y-m-d H:i'))
        ->set('booking_type', BookingType::HomeService->value)
        ->call('submit')
        ->assertHasErrors(['latitude'])
        ->assertSee('Pilih lokasi di peta.');

    expect(Service::count())->toBe(0);
});