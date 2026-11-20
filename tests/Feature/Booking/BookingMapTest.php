<?php

use App\Enums\BookingType;
use App\Livewire\BookingForm;
use Livewire\Livewire;

// Halaman publik: tes ini jalan sebagai tamu, bukan admin.
beforeEach(fn () => auth()->logout());

it('MAP-003: wadah peta membawa titik awal bengkel', function () {
    Livewire::test(BookingForm::class)
        ->set('booking_type', BookingType::HomeService->value)
        ->assertSeeHtml('data-center-lat="-7.7174465"')
        ->assertSeeHtml('data-center-lng="113.0755426"');
});
