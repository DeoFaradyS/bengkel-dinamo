<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use App\Models\ServicePart;
use App\Models\ServiceType;
use App\Models\SparePart;
use Livewire\Livewire;

// Servis dengan part. Tiap baris: [stok awal, qty dipakai].
// Hasil: [servis, [part1, part2, ...]]
function serviceWithParts(array $rows, ?ServiceStatus $status = null): array
{
    $service = $status
        ? Service::factory()->status($status)->create()
        : Service::factory()->create();

    $parts = [];

    foreach ($rows as [$stock, $qty]) {
        $part = SparePart::factory()->create(['stock' => $stock]);

        ServicePart::factory()->create([
            'service_id' => $service->id,
            'spare_part_id' => $part->id,
            'quantity' => $qty,
        ]);

        $parts[] = $part;
    }

    return [$service, $parts];
}

/* ------------------------------------------------------------------ */
/* STK: syncStock langsung di model                                    */
/* ------------------------------------------------------------------ */

it('STK-001: dua part, stok tiap part berkurang sesuai qty', function () {
    [$service, [$a, $b]] = serviceWithParts([[10, 2], [5, 1]]);

    $service->syncStock();

    expect($a->fresh()->stock)->toBe(8)
        ->and($b->fresh()->stock)->toBe(4);
});

it('STK-002: stock_synced_at terisi setelah sync', function () {
    [$service] = serviceWithParts([[10, 2]]);

    $service->syncStock();

    expect($service->fresh()->stock_synced_at)->not->toBeNull();
});

it('STK-003: sync dua kali, stok hanya berkurang sekali', function () {
    [$service, [$part]] = serviceWithParts([[10, 2]]);

    $service->syncStock();
    $service->fresh()->syncStock();

    expect($part->fresh()->stock)->toBe(8);
});

it('STK-004: stok kurang, error dan semua part batal berkurang', function () {
    // part1 cukup, part2 kurang. Part1 tidak boleh ikut berkurang.
    [$service, [$a, $b]] = serviceWithParts([[5, 2], [1, 3]]);

    expect(fn() => $service->syncStock())->toThrow(RuntimeException::class);

    expect($a->fresh()->stock)->toBe(5)
        ->and($b->fresh()->stock)->toBe(1)
        ->and($service->fresh()->stock_synced_at)->toBeNull();
});

it('STK-005: servis tanpa spare part, sync tidak error', function () {
    $service = Service::factory()->create();

    $service->syncStock();

    expect($service->fresh()->stock_synced_at)->not->toBeNull();
});

/* ------------------------------------------------------------------ */
/* STK: lewat form Filament                                            */
/* ------------------------------------------------------------------ */

it('STK-006: buat servis status non-Selesai, stok tidak berkurang', function (ServiceStatus $status) {
    $part = SparePart::factory()->create(['stock' => 10]);

    Livewire::test(CreateService::class)
        ->fillForm([
            'customer_name' => 'Pak Budi',
            'phone' => fake()->unique()->numerify('08##########'),
            'complaint' => 'Dinamo tidak mau engkol',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'status' => $status->value,
            'cancel_reason' => 'Pelanggan batal',
            'items' => [],
            'serviceParts' => [
                ['spare_part_id' => $part->id, 'quantity' => 2, 'unit_price' => 100000],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($part->fresh()->stock)->toBe(10);
})->with('statusNonSelesai');

it('STK-007: buat servis langsung Selesai, stok langsung berkurang', function () {
    $type = ServiceType::factory()->create();
    $part = SparePart::factory()->create(['stock' => 10]);

    // completeConfirmed = true melewati dialog konfirmasi.
    Livewire::test(CreateService::class)
        ->set('completeConfirmed', true)
        ->fillForm([
            'customer_name' => 'Pak Budi',
            'phone' => fake()->unique()->numerify('08##########'),
            'complaint' => 'Dinamo tidak mau engkol',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'status' => 'completed',
            'items' => [
                ['service_type_id' => $type->id, 'name' => $type->name, 'price' => 50000],
            ],
            'serviceParts' => [
                ['spare_part_id' => $part->id, 'quantity' => 2, 'unit_price' => 100000],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($part->fresh()->stock)->toBe(8);
});

it('STK-008: edit servis aktif jadi Selesai, stok berkurang', function () {
    $type = ServiceType::factory()->create();
    [$service, [$part]] = serviceWithParts([[10, 2]]);
    $service->update(['status' => ServiceStatus::InProgress]);

    $service->items()->create([
        'service_type_id' => $type->id,
        'name' => $type->name,
        'price' => 50000,
    ]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->set('completeConfirmed', true)
        ->fillForm(['status' => 'completed'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($part->fresh()->stock)->toBe(8)
        ->and($service->fresh()->stock_synced_at)->not->toBeNull();
});