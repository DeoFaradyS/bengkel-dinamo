<?php

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ServiceType;
use Livewire\Livewire;


// Servis langsung lewat model, tanpa form. Item: [[ServiceType, harga], ...]
function makeServiceWithItems(array $items = [], array $override = []): Service
{
    $service = Service::create(array_merge([
        'customer_name' => 'Pak Budi',
        'phone' => '08'.random_int(100000000, 999999999),
        'complaint' => 'Dinamo tidak mau engkol',
        'scheduled_at' => now()->addDay(),
        'status' => 'confirmed',
    ], $override));

    foreach ($items as [$type, $price]) {
        $service->items()->create([
            'service_type_id' => $type->id,
            'name' => $type->name,
            'price' => $price,
        ]);
    }

    return $service;
}

/* ------------------------------------------------------------------ */
/* TSJ: tambah servis dengan jasa                                      */
/* ------------------------------------------------------------------ */

it('TSJ-001: servis dengan dua jasa, dua baris tersimpan', function () {
    $a = ServiceType::factory()->create(['name' => 'Servis dinamo', 'default_price' => 50000]);
    $b = ServiceType::factory()->create(['name' => 'Ganti bearing', 'default_price' => 100000]);

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['items' => [
            ['service_type_id' => $a->id, 'name' => $a->name, 'price' => 50000],
            ['service_type_id' => $b->id, 'name' => $b->name, 'price' => 100000],
        ]]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServiceItem::count())->toBe(2);
});

it('TSJ-002: pilih jasa, name dan price terisi dari katalog', function () {
    $type = ServiceType::factory()->create(['name' => 'Ganti bearing', 'default_price' => 100000]);

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['items' => [
            ['service_type_id' => $type->id],
        ]]))
        ->assertFormSet(fn (array $state) => collect($state['items'])->first()['name'] === 'Ganti bearing'
            && (int) collect($state['items'])->first()['price'] === 100000);
});

it('TSJ-003: harga diubah admin, baris pakai harga baru, katalog tidak berubah', function () {
    $type = ServiceType::factory()->create(['default_price' => 100000]);

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['items' => [
            ['service_type_id' => $type->id, 'name' => $type->name, 'price' => 60000],
        ]]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect((float) ServiceItem::first()->price)->toBe(60000.0)
        ->and((float) $type->fresh()->default_price)->toBe(100000.0);
});

it('TSJ-004: jasa kosong di baris ditolak', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['items' => [
            ['service_type_id' => null, 'name' => null, 'price' => 50000],
        ]]))
        ->call('create')
        ->assertHasFormErrors(['items.0.service_type_id' => 'required']);

    expect(Service::count())->toBe(0);
});

it('TSJ-005: harga kosong ditolak', function () {
    $type = ServiceType::factory()->create();

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['items' => [
            ['service_type_id' => $type->id, 'name' => $type->name, 'price' => null],
        ]]))
        ->call('create')
        ->assertHasFormErrors(['items.0.price' => 'required']);

    expect(Service::count())->toBe(0);
});

it('TSJ-006: harga bukan angka ditolak', function () {
    $type = ServiceType::factory()->create();

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['items' => [
            ['service_type_id' => $type->id, 'name' => $type->name, 'price' => 'abc'],
        ]]))
        ->call('create')
        ->assertHasFormErrors(['items.0.price']);

    expect(Service::count())->toBe(0);
});

it('TSJ-007: harga negatif ditolak', function () {
    $type = ServiceType::factory()->create();

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['items' => [
            ['service_type_id' => $type->id, 'name' => $type->name, 'price' => -1],
        ]]))
        ->call('create')
        ->assertHasFormErrors(['items.0.price']);

    expect(Service::count())->toBe(0);
});

it('TSJ-008: harga 0 diterima', function () {
    $type = ServiceType::factory()->create();

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['items' => [
            ['service_type_id' => $type->id, 'name' => $type->name, 'price' => 0],
        ]]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect((float) ServiceItem::first()->price)->toBe(0.0);
});

/* ------------------------------------------------------------------ */
/* TSJ: snapshot dan hapus                                             */
/* ------------------------------------------------------------------ */

it('TSJ-009: katalog naik harga, harga di nota lama tetap', function () {
    $type = ServiceType::factory()->create(['default_price' => 100000]);
    $service = makeServiceWithItems([[$type, 100000]]);

    $type->update(['default_price' => 150000]);

    expect((float) $service->items()->first()->price)->toBe(100000.0);
});

it('TSJ-010: jasa dihapus dari katalog, baris nota tetap utuh', function () {
    $type = ServiceType::factory()->create(['name' => 'Ganti bearing']);
    $service = makeServiceWithItems([[$type, 100000]]);

    $type->delete();

    $item = $service->items()->first();

    expect($item)->not->toBeNull()
        ->and($item->service_type_id)->toBeNull()
        ->and($item->name)->toBe('Ganti bearing')
        ->and((float) $item->price)->toBe(100000.0);
});

it('TSJ-011: servis di-soft-delete, baris jasa tetap ada', function () {
    $a = ServiceType::factory()->create(['name' => 'Servis dinamo']);
    $b = ServiceType::factory()->create(['name' => 'Ganti bearing']);
    $service = makeServiceWithItems([[$a, 50000], [$b, 100000]]);

    $service->delete();

    expect(Service::withTrashed()->find($service->id))->not->toBeNull()
        ->and(ServiceItem::where('service_id', $service->id)->count())->toBe(2);
});

/* ------------------------------------------------------------------ */
/* TSJ: ubah servis                                                    */
/* ------------------------------------------------------------------ */

it('TSJ-012: edit servis, tambah baris jasa', function () {
    $a = ServiceType::factory()->create(['name' => 'Servis dinamo']);
    $b = ServiceType::factory()->create(['name' => 'Ganti bearing']);
    $service = makeServiceWithItems([[$a, 50000]]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['items' => [
            ['service_type_id' => $a->id, 'name' => $a->name, 'price' => 50000],
            ['service_type_id' => $b->id, 'name' => $b->name, 'price' => 100000],
        ]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->items()->count())->toBe(2);
});

it('TSJ-013: edit servis, hapus satu baris, baris lain aman', function () {
    $a = ServiceType::factory()->create(['name' => 'Servis dinamo']);
    $b = ServiceType::factory()->create(['name' => 'Ganti bearing']);
    $service = makeServiceWithItems([[$a, 50000], [$b, 100000]]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['items' => [
            ['service_type_id' => $b->id, 'name' => $b->name, 'price' => 100000],
        ]])
        ->call('save')
        ->assertHasNoFormErrors();

    $items = $service->items()->get();

    expect($items)->toHaveCount(1)
        ->and($items->first()->name)->toBe('Ganti bearing')
        ->and((float) $items->first()->price)->toBe(100000.0);
});

it('TSJ-014: edit servis, ubah harga satu baris, baris lain tidak berubah', function () {
    $a = ServiceType::factory()->create(['name' => 'Servis dinamo']);
    $b = ServiceType::factory()->create(['name' => 'Ganti bearing']);
    $service = makeServiceWithItems([[$a, 50000], [$b, 100000]]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['items' => [
            ['service_type_id' => $a->id, 'name' => $a->name, 'price' => 70000],
            ['service_type_id' => $b->id, 'name' => $b->name, 'price' => 100000],
        ]])
        ->call('save')
        ->assertHasNoFormErrors();

    $items = $service->items()->get()->keyBy('name');

    expect((float) $items['Servis dinamo']->price)->toBe(70000.0)
        ->and((float) $items['Ganti bearing']->price)->toBe(100000.0);
});

/* ------------------------------------------------------------------ */
/* TSJ: total dan aturan status                                        */
/* ------------------------------------------------------------------ */

it('TSJ-015: total jasa = jumlah harga semua baris', function () {
    $a = ServiceType::factory()->create(['name' => 'Servis dinamo']);
    $b = ServiceType::factory()->create(['name' => 'Ganti bearing']);
    $service = makeServiceWithItems([[$a, 50000], [$b, 100000]]);

    expect($service->fresh()->labor_total)->toBe(150000.0);
});

it('TSJ-016: status Dikonfirmasi tanpa jasa tersimpan, total 0', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['status' => 'confirmed', 'items' => []]))
        ->call('create')
        ->assertHasNoFormErrors();

    $service = Service::first();

    expect($service)->not->toBeNull()
        ->and($service->labor_total)->toBe(0.0);
});

it('TSJ-017: status Selesai tanpa jasa ditolak', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['status' => 'completed', 'items' => []]))
        ->call('create')
        ->assertHasFormErrors(['items']);

    expect(Service::count())->toBe(0);
});