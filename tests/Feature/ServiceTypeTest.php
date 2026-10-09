<?php

use App\Filament\Resources\ServiceTypes\Pages\CreateServiceType;
use App\Filament\Resources\ServiceTypes\Pages\EditServiceType;
use App\Filament\Resources\ServiceTypes\Pages\ListServiceTypes;
use App\Models\ServiceType;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create());
});

// Data form valid. Field yang mau diuji tinggal ditimpa lewat $override.
function typeData(array $override = []): array
{
    return array_merge([
        'name' => 'Servis dinamo',
        'default_price' => 50000,
    ], $override);
}

function makeType(array $override = []): ServiceType
{
    return ServiceType::create(array_merge([
        'name' => 'Servis dinamo',
        'default_price' => 50000,
    ], $override));
}

/* ------------------------------------------------------------------ */
/* TJS: tambah                                                         */
/* ------------------------------------------------------------------ */

it('TJS-001: submit dengan semua field valid', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(typeData())
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServiceType::where('name', 'Servis dinamo')->exists())->toBeTrue();
});

it('TJS-002: name dikosongkan ditolak', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(typeData(['name' => null]))
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);

    expect(ServiceType::count())->toBe(0);
});

it('TJS-003: name sama dengan jasa lain boleh', function () {
    makeType(['name' => 'Servis dinamo']);

    Livewire::test(CreateServiceType::class)
        ->fillForm(typeData(['name' => 'Servis dinamo']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServiceType::where('name', 'Servis dinamo')->count())->toBe(2);
});

it('TJS-004: default_price dikosongkan ditolak', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(typeData(['default_price' => null]))
        ->call('create')
        ->assertHasFormErrors(['default_price' => 'required']);

    expect(ServiceType::count())->toBe(0);
});

it('TJS-005: default_price negatif ditolak', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(typeData(['default_price' => -1]))
        ->call('create')
        ->assertHasFormErrors(['default_price']);

    expect(ServiceType::count())->toBe(0);
});

it('TJS-006: default_price bukan angka ditolak', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(typeData(['default_price' => 'abc']))
        ->call('create')
        ->assertHasFormErrors(['default_price']);

    expect(ServiceType::count())->toBe(0);
});

it('TJS-007: default_price 0 boleh', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(typeData(['default_price' => 0]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServiceType::where('name', 'Servis dinamo')->exists())->toBeTrue();
});

it('TJS-008: default_price di atas maksimal ditolak', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(typeData(['default_price' => 10000000000]))
        ->call('create')
        ->assertHasFormErrors(['default_price']);

    expect(ServiceType::count())->toBe(0);
});

/* ------------------------------------------------------------------ */
/* EJS: ubah                                                           */
/* ------------------------------------------------------------------ */

it('EJS-001: ubah data dengan valid value', function () {
    $type = makeType();

    Livewire::test(EditServiceType::class, ['record' => $type->getRouteKey()])
        ->fillForm(['name' => 'Servis dinamo besar', 'default_price' => 75000])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($type->fresh()->name)->toBe('Servis dinamo besar')
        ->and((float) $type->fresh()->default_price)->toBe(75000.0);
});

it('EJS-002: name dikosongkan ditolak', function () {
    $type = makeType();

    Livewire::test(EditServiceType::class, ['record' => $type->getRouteKey()])
        ->fillForm(['name' => null])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});

/* ------------------------------------------------------------------ */
/* HJS: hapus                                                          */
/* ------------------------------------------------------------------ */

it('HJS-001: hapus jasa berhasil', function () {
    $type = makeType();

    Livewire::test(ListServiceTypes::class)
        ->callAction(TestAction::make('delete')->table($type));

    $this->assertModelMissing($type);
});

// Baru bisa ditulis setelah tabel service_items ada (langkah berikutnya).
it('HJS-002: hapus jasa yang dipakai servis, baris servis tetap utuh')->todo();

/* ------------------------------------------------------------------ */
/* SJS: search                                                         */
/* ------------------------------------------------------------------ */

it('SJS-001: cari pakai nama', function () {
    $a = makeType(['name' => 'Servis dinamo']);
    $b = makeType(['name' => 'Ganti bearing']);

    Livewire::test(ListServiceTypes::class)
        ->searchTable('bearing')
        ->assertCanSeeTableRecords([$b])
        ->assertCanNotSeeTableRecords([$a]);
});

it('SJS-002: cari kata yang tidak ada, tabel kosong', function () {
    $a = makeType();

    Livewire::test(ListServiceTypes::class)
        ->searchTable('tidak-ada-xyz')
        ->assertCanNotSeeTableRecords([$a]);
});