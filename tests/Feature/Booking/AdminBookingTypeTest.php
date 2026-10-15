<?php

use App\Enums\BookingType;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('ADM-001: tabel punya kolom tipe layanan', function () {
    Livewire::test(ListServices::class)
        ->assertTableColumnExists('booking_type');
});

it('ADM-002: badge menampilkan label tipe', function () {
    Service::factory()->create([
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
    ]);

    Livewire::test(ListServices::class)
        ->assertSee('Home Service');
});

it('ADM-003: filter tipe memisahkan home service dan workshop', function () {
    $home = Service::factory()->create([
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
    ]);
    $workshop = Service::factory()->create(['booking_type' => BookingType::Workshop]);

    Livewire::test(ListServices::class)
        ->filterTable('booking_type', BookingType::HomeService->value)
        ->assertCanSeeTableRecords([$home])
        ->assertCanNotSeeTableRecords([$workshop]);
});

it('ADM-004: tombol Buka Peta muncul hanya kalau koordinat ada', function () {
    $home = Service::factory()->create([
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
    ]);
    $workshop = Service::factory()->create(['booking_type' => BookingType::Workshop]);

    Livewire::test(ListServices::class)
        ->assertActionVisible(TestAction::make('map')->table($home))
        ->assertActionHidden(TestAction::make('map')->table($workshop));
});