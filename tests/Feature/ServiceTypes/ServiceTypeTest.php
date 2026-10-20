<?php

use App\Filament\Resources\ServiceTypes\Pages\ManageServiceTypes;
use App\Filament\Resources\ServiceTypes\ServiceTypeResource;
use App\Models\Service;
use App\Models\ServiceType;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

// Data form valid. Field yang mau diuji tinggal ditimpa lewat $override.
function typeData(array $override = []): array
{
    return array_merge([
        'name' => 'Servis dinamo',
        'default_price' => 50000,
    ], $override);
}

/* ------------------------------------------------------------------ */
/* KJS: daftar dan halaman                                             */
/* ------------------------------------------------------------------ */

it('KJS-001: tabel menampilkan daftar jasa', function () {
    $a = ServiceType::factory()->create();
    $b = ServiceType::factory()->create();

    Livewire::test(ManageServiceTypes::class)
        ->assertCanSeeTableRecords([$a, $b]);
});

it('KJS-002: tidak ada halaman Tambah dan Ubah terpisah, semua lewat modal', function () {
    expect(ServiceTypeResource::getPages())
        ->toHaveKey('index')
        ->not->toHaveKey('create')
        ->not->toHaveKey('edit');
});

/* ------------------------------------------------------------------ */
/* TJS: tambah lewat modal                                             */
/* ------------------------------------------------------------------ */

it('TJS-001: submit dengan semua field valid', function () {
    Livewire::test(ManageServiceTypes::class)
        ->callAction('create', data: typeData())
        ->assertHasNoActionErrors();

    expect(ServiceType::where('name', 'Servis dinamo')->exists())->toBeTrue();
});

it('TJS-002: name dikosongkan ditolak', function () {
    Livewire::test(ManageServiceTypes::class)
        ->callAction('create', data: typeData(['name' => null]))
        ->assertHasActionErrors(['name' => 'required']);

    expect(ServiceType::count())->toBe(0);
});

it('TJS-003: name sama dengan jasa lain boleh', function () {
    ServiceType::factory()->create(['name' => 'Servis dinamo']);

    Livewire::test(ManageServiceTypes::class)
        ->callAction('create', data: typeData(['name' => 'Servis dinamo']))
        ->assertHasNoActionErrors();

    expect(ServiceType::where('name', 'Servis dinamo')->count())->toBe(2);
});

it('TJS-004: default_price dikosongkan ditolak', function () {
    Livewire::test(ManageServiceTypes::class)
        ->callAction('create', data: typeData(['default_price' => null]))
        ->assertHasActionErrors(['default_price' => 'required']);

    expect(ServiceType::count())->toBe(0);
});

it('TJS-005: default_price negatif ditolak', function () {
    Livewire::test(ManageServiceTypes::class)
        ->callAction('create', data: typeData(['default_price' => -1]))
        ->assertHasActionErrors(['default_price']);

    expect(ServiceType::count())->toBe(0);
});

it('TJS-006: default_price bukan angka ditolak', function () {
    Livewire::test(ManageServiceTypes::class)
        ->callAction('create', data: typeData(['default_price' => 'abc']))
        ->assertHasActionErrors(['default_price']);

    expect(ServiceType::count())->toBe(0);
});

it('TJS-007: default_price 0 boleh', function () {
    Livewire::test(ManageServiceTypes::class)
        ->callAction('create', data: typeData(['default_price' => 0]))
        ->assertHasNoActionErrors();

    expect(ServiceType::where('name', 'Servis dinamo')->exists())->toBeTrue();
});

it('TJS-008: default_price di atas maksimal ditolak', function () {
    Livewire::test(ManageServiceTypes::class)
        ->callAction('create', data: typeData(['default_price' => 10000000000]))
        ->assertHasActionErrors(['default_price']);

    expect(ServiceType::count())->toBe(0);
});

/* ------------------------------------------------------------------ */
/* EJS: ubah lewat modal                                               */
/* ------------------------------------------------------------------ */

it('EJS-001: ubah data dengan valid value', function () {
    $type = ServiceType::factory()->create();

    Livewire::test(ManageServiceTypes::class)
        ->callAction(
            TestAction::make('edit')->table($type),
            data: ['name' => 'Servis dinamo besar', 'default_price' => 75000],
        )
        ->assertHasNoActionErrors();

    expect($type->fresh()->name)->toBe('Servis dinamo besar')
        ->and((float) $type->fresh()->default_price)->toBe(75000.0);
});

it('EJS-002: name dikosongkan ditolak', function () {
    $type = ServiceType::factory()->create();

    Livewire::test(ManageServiceTypes::class)
        ->callAction(TestAction::make('edit')->table($type), data: ['name' => null])
        ->assertHasActionErrors(['name' => 'required']);
});

/* ------------------------------------------------------------------ */
/* HJS: hapus                                                          */
/* ------------------------------------------------------------------ */

it('HJS-001: hapus jasa berhasil', function () {
    $type = ServiceType::factory()->create();

    Livewire::test(ManageServiceTypes::class)
        ->callAction(TestAction::make('delete')->table($type));

    $this->assertModelMissing($type);
});

it('HJS-002: hapus jasa yang dipakai servis, baris servis tetap utuh', function () {
    $type = ServiceType::factory()->create(['name' => 'Ganti bearing']);

    $service = Service::create([
        'customer_name' => 'Pak Budi',
        'phone' => '081234500001',
        'complaint' => 'Dinamo tidak mau engkol',
        'scheduled_at' => now()->addDay(),
        'status' => 'confirmed',
    ]);

    $service->items()->create([
        'service_type_id' => $type->id,
        'name' => $type->name,
        'price' => 100000,
    ]);

    Livewire::test(ManageServiceTypes::class)
        ->callAction(TestAction::make('delete')->table($type));

    $item = $service->items()->first();

    expect($item)->not->toBeNull()
        ->and($item->service_type_id)->toBeNull()
        ->and($item->name)->toBe('Ganti bearing');
});

/* ------------------------------------------------------------------ */
/* SJS: search                                                         */
/* ------------------------------------------------------------------ */

it('SJS-001: cari pakai nama', function () {
    $a = ServiceType::factory()->create(['name' => 'Servis dinamo']);
    $b = ServiceType::factory()->create(['name' => 'Ganti bearing']);

    Livewire::test(ManageServiceTypes::class)
        ->searchTable('bearing')
        ->assertCanSeeTableRecords([$b])
        ->assertCanNotSeeTableRecords([$a]);
});

it('SJS-002: cari kata yang tidak ada, tabel kosong', function () {
    $a = ServiceType::factory()->create();

    Livewire::test(ManageServiceTypes::class)
        ->searchTable('tidak-ada-xyz')
        ->assertCanNotSeeTableRecords([$a]);
});