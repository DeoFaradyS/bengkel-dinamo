<?php

use App\Enums\BookingType;
use Livewire\Livewire;

it('LOC-001: Home Service, tombol Lokasi saya tampil', function () {
    Livewire::test('booking-form')
        ->set('booking_type', BookingType::HomeService->value)
        ->assertSee('Lokasi saya');
});

it('LOC-002: Ke Bengkel, tombol Lokasi saya tidak ada', function () {
    Livewire::test('booking-form')
        ->set('booking_type', BookingType::Workshop->value)
        ->assertDontSee('Lokasi saya');
});