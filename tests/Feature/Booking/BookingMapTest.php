<?php

use App\Enums\BookingType;
use App\Livewire\BookingForm;
use Livewire\Livewire;

// Halaman publik: tes ini jalan sebagai tamu, bukan admin.
beforeEach(fn () => auth()->logout());

it('MAP-001: workshop tidak punya wadah peta', function () {
    Livewire::test(BookingForm::class)
        ->assertDontSeeHtml('id="booking-map"');
});

it('MAP-002: home service punya wadah peta', function () {
    Livewire::test(BookingForm::class)
        ->set('booking_type', BookingType::HomeService->value)
        ->assertSeeHtml('id="booking-map"');
});

it('MAP-003: wadah peta membawa titik awal bengkel', function () {
    Livewire::test(BookingForm::class)
        ->set('booking_type', BookingType::HomeService->value)
        ->assertSeeHtml('data-center-lat="-7.7174465"')
        ->assertSeeHtml('data-center-lng="113.0755426"');
});

it('MAP-004: placeholder lama sudah hilang', function () {
    Livewire::test(BookingForm::class)
        ->set('booking_type', BookingType::HomeService->value)
        ->assertDontSee('Peta lokasi menyusul.');
});