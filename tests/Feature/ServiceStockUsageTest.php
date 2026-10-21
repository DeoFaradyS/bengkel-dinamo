<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Enums\StockMovementType;
use App\Models\Service;
use App\Models\SparePart;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Livewire\Livewire;
use App\Models\ServiceType;

function serviceWithPart(int $stock, int $qty): array
{
    $part = SparePart::create(['code' => 'SP-900', 'name' => 'Bendix', 'stock' => $stock]);

    $service = Service::create([
        'customer_name' => 'Budi',
        'phone' => '081234567890',
        'complaint' => 'Mati total',
        'scheduled_at' => now(),
        'status' => ServiceStatus::Completed,
        'booking_type' => BookingType::Workshop,
    ]);

    $service->serviceParts()->create([
        'spare_part_id' => $part->id,
        'quantity' => $qty,
        'unit_price' => 50000,
    ]);

    return [$service, $part];
}

it('SUS-001: servis selesai mencatat pemakaian di riwayat stok', function () {
    [$service, $part] = serviceWithPart(10, 2);

    $service->syncStock();

    $movement = $part->stockMovements()->where('type', StockMovementType::Usage)->first();

    expect($part->fresh()->stock)->toBe(8)
        ->and($movement->quantity)->toBe(-2)
        ->and($movement->stock_after)->toBe(8)
        ->and($movement->service_id)->toBe($service->id);
});

it('SUS-002: syncStock dipanggil dua kali, stok tidak berkurang dua kali', function () {
    [$service, $part] = serviceWithPart(10, 2);

    $service->syncStock();
    $service->fresh()->syncStock();

    expect($part->fresh()->stock)->toBe(8)
        ->and($part->stockMovements()->where('type', StockMovementType::Usage)->count())->toBe(1);
});

it('SUS-003: stok kurang, servis gagal dan tidak ada catatan', function () {
    [$service, $part] = serviceWithPart(1, 2);

    expect(fn() => $service->syncStock())->toThrow(RuntimeException::class);

    expect($part->fresh()->stock)->toBe(1)
        ->and($part->stockMovements()->where('type', StockMovementType::Usage)->exists())->toBeFalse();
});


it('SUS-004: servis aktif jadi Selesai lewat halaman Edit, stok turun dan riwayat tercatat', function () {
    $this->actingAs(User::factory()->create());

    [$service, $part] = serviceWithPart(10, 2);
    $service->update(['status' => ServiceStatus::InProgress]);

    $type = ServiceType::create(['name' => 'Servis dinamo', 'default_price' => 65000]);

    $service->items()->create([
        'service_type_id' => $type->id,
        'name' => $type->name,
        'price' => 65000,
    ]);

    Livewire::test(EditService::class, ['record' => $service->getKey()])
        ->fillForm(['status' => ServiceStatus::Completed->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($part->fresh()->stock)->toBe(8)
        ->and($part->stockMovements()->where('type', StockMovementType::Usage)->count())->toBe(1);
});

it('SUS-005: seeder hanya menandai sinkron servis Selesai', function () {
    $this->seed();

    expect(Service::whereNotNull('stock_synced_at')->where('status', '!=', 'completed')->count())->toBe(0)
        ->and(Service::where('status', 'completed')->whereNull('stock_synced_at')->count())->toBe(0);
});