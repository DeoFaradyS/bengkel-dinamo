<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\StatusChart;
use App\Filament\Widgets\TopPartsChart;
use App\Filament\Widgets\TopServicesChart;
use App\Filament\Widgets\TypeChart;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\SparePart;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

// Servis selesai bertanggal sekarang, jadi masuk periode bawaan (bulan ini).
function cmpCompleted(): Service
{
    return Service::factory()->create([
        'status' => ServiceStatus::Completed,
        'scheduled_at' => now(),
        'created_at' => now(),
        'booking_type' => BookingType::Workshop,
    ]);
}

it('CMP-001: donat status tampil dengan semua label status', function () {
    Livewire::test(StatusChart::class)
        ->assertSee('Komposisi Status Servis')
        ->assertSee('Menunggu Konfirmasi')
        ->assertSee('Selesai')
        ->assertSee('Ditolak');
});

it('CMP-002: donat tipe layanan tampil dengan kedua label', function () {
    Livewire::test(TypeChart::class)
        ->assertSee('Home Service vs Ke Bengkel')
        ->assertSee(BookingType::HomeService->getLabel())
        ->assertSee(BookingType::Workshop->getLabel());
});

it('CMP-003: jasa terlaris menampilkan nama jasa dari servis selesai', function () {
    cmpCompleted()->items()->create([
        'service_type_id' => ServiceType::factory()->create()->id,
        'name' => 'Servis dinamo',
        'price' => 100000,
    ]);

    Livewire::test(TopServicesChart::class)
        ->assertSee('Jasa Terlaris')
        ->assertSee('Servis dinamo');
});

it('CMP-004: spare part terlaris menampilkan nama part dari servis selesai', function () {
    cmpCompleted()->serviceParts()->create([
        'spare_part_id' => SparePart::factory()->create(['name' => 'Brush Karbon'])->id,
        'quantity' => 2,
        'unit_price' => 10000,
    ]);

    Livewire::test(TopPartsChart::class)
        ->assertSee('Spare Part Terlaris')
        ->assertSee('Brush Karbon');
});

it('CMP-005: dashboard memuat keempat grafik komposisi', function () {
    Livewire::test(Dashboard::class)
        ->assertSeeLivewire(StatusChart::class)
        ->assertSeeLivewire(TypeChart::class)
        ->assertSeeLivewire(TopServicesChart::class)
        ->assertSeeLivewire(TopPartsChart::class);
});