<?php

use App\Filament\Resources\Services\Pages\CreateService;
use App\Models\Service;
use App\Models\ServicePart;
use App\Models\ServiceType;
use App\Models\SparePart;
use Livewire\Livewire;

// Pembungkus tipis: isi 'serviceParts', sisanya dari serviceFormData() di Pest.php.
function partFormData(array $serviceParts = [], array $override = []): array
{
    return serviceFormData(array_merge(['serviceParts' => $serviceParts], $override));
}

/* ------------------------------------------------------------------ */
/* SPF: simpan part                                                    */
/* ------------------------------------------------------------------ */

it('SPF-001: servis dengan satu part, baris tersimpan benar', function () {
    $part = SparePart::factory()->create(['stock' => 10]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $part->id, 'quantity' => 2, 'unit_price' => 100000],
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    $row = ServicePart::first();

    expect($row->spare_part_id)->toBe($part->id)
        ->and($row->quantity)->toBe(2)
        ->and((float) $row->unit_price)->toBe(100000.0);
});

it('SPF-002: servis dengan dua part, dua baris tersimpan', function () {
    $a = SparePart::factory()->create(['stock' => 10]);
    $b = SparePart::factory()->create(['stock' => 10]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $a->id, 'quantity' => 1, 'unit_price' => 100000],
            ['spare_part_id' => $b->id, 'quantity' => 3, 'unit_price' => 50000],
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServicePart::count())->toBe(2);
});

it('SPF-003: servis tanpa spare part tetap tersimpan', function () {
    Livewire::test(CreateService::class)
        ->fillForm(partFormData([]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::count())->toBe(1)
        ->and(ServicePart::count())->toBe(0);
});

/* ------------------------------------------------------------------ */
/* SPF: validasi qty                                                   */
/* ------------------------------------------------------------------ */

it('SPF-004: qty melebihi stok ditolak', function () {
    $part = SparePart::factory()->create(['stock' => 3]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $part->id, 'quantity' => 4, 'unit_price' => 100000],
        ]))
        ->call('create')
        ->assertHasFormErrors(['serviceParts.0.quantity']);

    expect(Service::count())->toBe(0);
});

it('SPF-005: qty sama dengan stok diterima', function () {
    $part = SparePart::factory()->create(['stock' => 3]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $part->id, 'quantity' => 3, 'unit_price' => 100000],
        ]))
        ->call('create')
        ->assertHasNoFormErrors();
});

it('SPF-006: qty 0 ditolak', function () {
    $part = SparePart::factory()->create(['stock' => 10]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $part->id, 'quantity' => 0, 'unit_price' => 100000],
        ]))
        ->call('create')
        ->assertHasFormErrors(['serviceParts.0.quantity']);
});

it('SPF-007: qty kosong ditolak', function () {
    $part = SparePart::factory()->create(['stock' => 10]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $part->id, 'quantity' => null, 'unit_price' => 100000],
        ]))
        ->call('create')
        ->assertHasFormErrors(['serviceParts.0.quantity' => 'required']);
});

/* ------------------------------------------------------------------ */
/* SPF: validasi part dan harga                                        */
/* ------------------------------------------------------------------ */

it('SPF-008: part kosong ditolak', function () {
    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => null, 'quantity' => 1, 'unit_price' => 100000],
        ]))
        ->call('create')
        ->assertHasFormErrors(['serviceParts.0.spare_part_id' => 'required']);
});

it('SPF-009: harga satuan kosong ditolak', function () {
    $part = SparePart::factory()->create(['stock' => 10]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $part->id, 'quantity' => 1, 'unit_price' => null],
        ]))
        ->call('create')
        ->assertHasFormErrors(['serviceParts.0.unit_price' => 'required']);
});

it('SPF-010: harga satuan negatif ditolak', function () {
    $part = SparePart::factory()->create(['stock' => 10]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $part->id, 'quantity' => 1, 'unit_price' => -1],
        ]))
        ->call('create')
        ->assertHasFormErrors(['serviceParts.0.unit_price']);
});

it('SPF-011: part yang sama dua kali di satu servis ditolak', function () {
    $part = SparePart::factory()->create(['stock' => 10]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $part->id, 'quantity' => 1, 'unit_price' => 100000],
            ['spare_part_id' => $part->id, 'quantity' => 1, 'unit_price' => 100000],
        ]))
        ->call('create')
        ->assertHasFormErrors();

    expect(Service::count())->toBe(0);
});

it('SPF-012: pilih part, harga satuan terisi dari harga part', function () {
    $part = SparePart::factory()->create(['stock' => 10, 'price' => 150000]);

    Livewire::test(CreateService::class)
        ->fillForm(partFormData([
            ['spare_part_id' => $part->id],
        ]))
        ->assertFormSet(fn (array $state) => (int) collect($state['serviceParts'])->first()['unit_price'] === 150000);
});

/* ------------------------------------------------------------------ */
/* SPF: total                                                          */
/* ------------------------------------------------------------------ */

it('SPF-013: subtotal = qty x harga satuan', function () {
    $row = ServicePart::factory()->create(['quantity' => 3, 'unit_price' => 50000]);

    expect($row->subtotal)->toBe(150000.0);
});

it('SPF-014: total spare part = jumlah semua subtotal', function () {
    $service = Service::factory()->create();

    ServicePart::factory()->create(['service_id' => $service->id, 'quantity' => 2, 'unit_price' => 100000]);
    ServicePart::factory()->create(['service_id' => $service->id, 'quantity' => 1, 'unit_price' => 50000]);

    expect($service->fresh()->parts_total)->toBe(250000.0);
});

it('SPF-015: grand total = total jasa + total spare part', function () {
    $type = ServiceType::create(['name' => 'Ganti bearing', 'default_price' => 50000]);
    $service = Service::factory()->create();

    $service->items()->create([
        'service_type_id' => $type->id,
        'name' => $type->name,
        'price' => 50000,
    ]);

    ServicePart::factory()->create(['service_id' => $service->id, 'quantity' => 2, 'unit_price' => 100000]);

    expect($service->fresh()->grand_total)->toBe(250000.0);
});