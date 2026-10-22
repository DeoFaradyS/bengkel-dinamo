<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use App\Models\ServicePart;
use App\Models\ServiceType;
use App\Models\SparePart;
use Livewire\Livewire;

it('STK-010: stok kurang saat Selesai lewat form, tidak error, status dan stok tetap', function () {
    $type = ServiceType::factory()->create();
    $service = Service::factory()->create();

    $service->items()->create([
        'service_type_id' => $type->id,
        'name' => $type->name,
        'price' => 50000,
    ]);

    $part = SparePart::factory()->create(['stock' => 10]);

    ServicePart::factory()->create([
        'service_id' => $service->id,
        'spare_part_id' => $part->id,
        'quantity' => 5,
        'unit_price' => 100000,
    ]);

    // Stok turun setelah servis dibuat, jadi kurang dari qty.
    $part->update(['stock' => 2]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['status' => 'completed'])
        ->call('save');

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed)
        ->and($part->fresh()->stock)->toBe(2);
});

it('STK-011: edit ke status batal atau tolak, stok tidak berkurang', function (ServiceStatus $status) {
    $service = Service::factory()->create(['status' => ServiceStatus::Pending]);
    $part = SparePart::factory()->create(['stock' => 10]);

    ServicePart::factory()->create([
        'service_id' => $service->id,
        'spare_part_id' => $part->id,
        'quantity' => 3,
        'unit_price' => 100000,
    ]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['status' => $status->value, 'cancel_reason' => 'Pelanggan batal'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->fresh()->status)->toBe($status)
        ->and($part->fresh()->stock)->toBe(10);
})->with([
    'dibatalkan' => [ServiceStatus::Cancelled],
    'ditolak' => [ServiceStatus::Rejected],
]);