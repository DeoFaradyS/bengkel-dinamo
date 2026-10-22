<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Pages\ViewService;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

/* ------------------------------------------------------------------ */
/* KNC: aturan final                                                   */
/* ------------------------------------------------------------------ */

it('KNC-001: status aktif bukan final', function (ServiceStatus $status) {
    expect($status->isFinal())->toBeFalse();
})->with('statusAktif');

it('KNC-002: Selesai, Dibatalkan, Ditolak adalah final', function (ServiceStatus $status) {
    expect($status->isFinal())->toBeTrue();
})->with('statusFinal');

it('KNC-003: servis aktif boleh diedit', function (ServiceStatus $status) {
    $service = Service::factory()->status($status)->create();

    expect(ServiceResource::canEdit($service))->toBeTrue();
})->with('statusAktif');

it('KNC-004: servis final tidak boleh diedit', function (ServiceStatus $status) {
    $service = Service::factory()->status($status)->create();

    expect(ServiceResource::canEdit($service))->toBeFalse();
})->with('statusFinal');

/* ------------------------------------------------------------------ */
/* KNC: halaman                                                        */
/* ------------------------------------------------------------------ */

it('KNC-005: buka halaman Edit servis aktif berhasil', function (ServiceStatus $status) {
    $service = Service::factory()->status($status)->create();

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->assertSuccessful();
})->with('statusAktif');

it('KNC-006: buka halaman Edit servis final ditolak 403', function (ServiceStatus $status) {
    $service = Service::factory()->status($status)->create();

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->assertForbidden();
})->with('statusFinal');

it('KNC-007: buka halaman Lihat servis final berhasil', function (ServiceStatus $status) {
    $service = Service::factory()->status($status)->create();

    Livewire::test(ViewService::class, ['record' => $service->getRouteKey()])
        ->assertSuccessful();
})->with('statusFinal');

/* ------------------------------------------------------------------ */
/* KNC: tombol di tabel                                                */
/* ------------------------------------------------------------------ */

it('KNC-008: servis aktif, klik baris ke halaman Edit', function () {
    $service = Service::factory()->create();

    $url = Livewire::test(ListServices::class)
        ->instance()
        ->getTable()
        ->getRecordUrl($service);

    expect($url)->toBe(ServiceResource::getUrl('edit', ['record' => $service]));
});

it('KNC-009: servis final, klik baris ke halaman Lihat', function (ServiceStatus $status) {
    $service = Service::factory()->status($status)->create();

    $url = Livewire::test(ListServices::class)
        ->instance()
        ->getTable()
        ->getRecordUrl($service);

    expect($url)->toBe(ServiceResource::getUrl('view', ['record' => $service]));
})->with('statusFinal');

it('KNC-010: servis aktif punya tombol Ubah tanpa Lihat', function () {
    $service = Service::factory()->create();

    Livewire::test(ListServices::class)
        ->assertActionVisible(TestAction::make('edit')->table($service))
        ->assertActionHidden(TestAction::make('view')->table($service));
});

it('KNC-011: servis final punya tombol Lihat tanpa Ubah', function (ServiceStatus $status) {
    $service = Service::factory()->status($status)->create();

    Livewire::test(ListServices::class)
        ->set('activeTab', $status->value)
        ->assertActionVisible(TestAction::make('view')->table($service))
        ->assertActionHidden(TestAction::make('edit')->table($service));
})->with('statusFinal');