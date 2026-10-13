<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Pages\ViewService;
use App\Models\Service;
use App\Models\ServicePart;
use App\Models\ServiceType;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

// Servis Selesai lengkap: satu jasa + satu part.
function completedServiceWithData(): Service
{
    $type = ServiceType::create(['name' => 'Ganti bearing', 'default_price' => 50000]);
    $service = Service::factory()->completed()->create();

    $service->items()->create([
        'service_type_id' => $type->id,
        'name' => $type->name,
        'price' => 50000,
    ]);

    ServicePart::factory()->create([
        'service_id' => $service->id,
        'quantity' => 2,
        'unit_price' => 100000,
    ]);

    return $service;
}

/* ------------------------------------------------------------------ */
/* INV: nomor dan isi PDF                                              */
/* ------------------------------------------------------------------ */

it('INV-001: nomor invoice berformat INV-TANGGAL-ID4DIGIT', function () {
    $service = Service::factory()->create(['scheduled_at' => '2026-10-14 10:00:00']);

    expect($service->invoiceNumber())
        ->toBe('INV-20261014-' . str_pad((string) $service->id, 4, '0', STR_PAD_LEFT));
});

it('INV-002: invoicePdf menghasilkan file PDF', function () {
    $service = completedServiceWithData();

    $pdf = $service->invoicePdf();

    expect($pdf)->not->toBeEmpty()
        ->and(substr($pdf, 0, 4))->toBe('%PDF');
});

it('INV-003: invoicePdf tanpa jasa dan tanpa part tidak error', function () {
    $service = Service::factory()->completed()->create();

    expect(substr($service->invoicePdf(), 0, 4))->toBe('%PDF');
});

/* ------------------------------------------------------------------ */
/* INV: tombol di halaman Lihat                                        */
/* ------------------------------------------------------------------ */

it('INV-004: halaman Lihat servis Selesai, tombol Invoice tampil', function () {
    $service = completedServiceWithData();

    Livewire::test(ViewService::class, ['record' => $service->getRouteKey()])
        ->assertActionVisible('invoice');
});

it('INV-005: halaman Lihat servis non-Selesai, tombol Invoice tersembunyi', function (ServiceStatus $status) {
    $service = Service::factory()->status($status)->create();

    Livewire::test(ViewService::class, ['record' => $service->getRouteKey()])
        ->assertActionHidden('invoice');
})->with('statusNonSelesai');

/* ------------------------------------------------------------------ */
/* INV: tombol di tabel                                                */
/* ------------------------------------------------------------------ */

it('INV-006: tabel servis Selesai, tombol Invoice tampil', function () {
    $service = completedServiceWithData();

    Livewire::test(ListServices::class)
        ->set('activeTab', 'completed')
        ->assertActionVisible(TestAction::make('invoice')->table($service));
});

it('INV-007: tabel servis aktif, tombol Invoice tersembunyi', function () {
    $service = Service::factory()->create();

    Livewire::test(ListServices::class)
        ->assertActionHidden(TestAction::make('invoice')->table($service));
});

it('INV-008: tabel servis Dibatalkan, tombol Invoice tersembunyi', function () {
    $service = Service::factory()->cancelled()->create();

    Livewire::test(ListServices::class)
        ->set('activeTab', 'cancelled')
        ->assertActionHidden(TestAction::make('invoice')->table($service));
});

/* ------------------------------------------------------------------ */
/* INV: unduh                                                          */
/* ------------------------------------------------------------------ */

it('INV-009: klik Invoice di tabel, file PDF terunduh', function () {
    $service = completedServiceWithData();

    Livewire::test(ListServices::class)
        ->set('activeTab', 'completed')
        ->callAction(TestAction::make('invoice')->table($service))
        ->assertFileDownloaded($service->invoiceNumber() . '.pdf');
});