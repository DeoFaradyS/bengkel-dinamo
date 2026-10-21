<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\ViewService;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('DTL-001: detail tampil ringkasan Total Servis', function () {
    $type = ServiceType::factory()->create();
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'booking_type' => BookingType::Workshop,
    ]);
    $service->items()->forceCreate([
        'service_type_id' => $type->id,
        'name' => $type->name,
        'price' => 150000,
    ]);

    Livewire::test(ViewService::class, ['record' => $service->getRouteKey()])
        ->assertSee('Total Servis')
        ->assertSee('150.000');
});

it('DTL-002: servis Dibatalkan menampilkan alasan', function () {
    $service = Service::factory()->create([
        'status' => ServiceStatus::Cancelled,
        'cancel_reason' => 'Biaya melebihi harga unit baru',
    ]);

    Livewire::test(ViewService::class, ['record' => $service->getRouteKey()])
        ->assertSee('Alasan')
        ->assertSee('Biaya melebihi harga unit baru');
});

it('DTL-003: servis non-batal tidak menampilkan alasan', function () {
    $service = Service::factory()->create(['status' => ServiceStatus::Confirmed]);

    Livewire::test(ViewService::class, ['record' => $service->getRouteKey()])
        ->assertDontSee('Alasan');
});

it('DTL-004: Home Service menampilkan alamat dan link peta', function () {
    $service = Service::factory()->create([
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
        'address' => 'depan Indomaret, pagar hijau',
    ]);

    Livewire::test(ViewService::class, ['record' => $service->getRouteKey()])
        ->assertSee('depan Indomaret, pagar hijau')
        ->assertSee('Buka di Google Maps');
});

it('DTL-005: Ke Bengkel tidak menampilkan link peta', function () {
    $service = Service::factory()->create(['booking_type' => BookingType::Workshop]);

    Livewire::test(ViewService::class, ['record' => $service->getRouteKey()])
        ->assertDontSee('Buka di Google Maps');
});