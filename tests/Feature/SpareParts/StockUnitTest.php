<?php

use App\Enums\StockMovementType;
use App\Enums\StockReason;
use App\Enums\StockUnit;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Models\Service;
use App\Models\ServicePart;
use App\Models\SparePart;
use App\Support\Qty;
use Livewire\Livewire;

// Satuan suku cadang dan jumlah desimal (21 Nov 2026, aturan 33). Kode modul: UNT.
// kg/meter/liter boleh pecahan sampai 2 angka, pcs/set/pasang harus bulat.

function untKawat(float $stok = 6.5): SparePart
{
    return SparePart::factory()->create(['name' => 'Kabel Email', 'unit' => StockUnit::Kg, 'stock' => $stok, 'price' => 130000]);
}

it('UNT-001: satuan kg, stok berkurang pecahan dan riwayat mencatat pecahannya', function () {
    $kawat = untKawat(6.5);

    $kawat->addStock(-0.35, StockMovementType::Correction, null, null, StockReason::Damaged);

    expect($kawat->fresh()->stock)->toBe(6.15)
        ->and($kawat->stockMovements()->latest('id')->first())
        ->quantity_change->toBe(-0.35)
        ->stock_after->toBe(6.15);
});

it('UNT-002: satuan pcs, jumlah pecahan ditolak dan stok tidak berubah', function () {
    $bearing = SparePart::factory()->create(['unit' => StockUnit::Pcs, 'stock' => 10]);

    expect(fn() => $bearing->addStock(-0.5, StockMovementType::Usage))
        ->toThrow(RuntimeException::class, 'harus bilangan bulat');

    expect($bearing->fresh()->stock)->toBe(10);
});

it('UNT-003: batas, stok kg tidak boleh di bawah 0 walau selisihnya pecahan', function () {
    $kawat = untKawat(0.3);

    expect(fn() => $kawat->addStock(-0.31, StockMovementType::Usage))->toThrow(RuntimeException::class);

    $kawat->addStock(-0.3, StockMovementType::Usage);

    expect($kawat->fresh()->stock)->toBe(0);
});

it('UNT-004: servis memakai 0,35 kg kawat, subtotal dan pengurangan stok benar', function () {
    $kawat = untKawat(6.5);

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['status' => 'in_progress', 'serviceParts' => [
            ['spare_part_id' => $kawat->id, 'quantity' => 0.35, 'unit_price' => 130000],
        ]]))
        ->call('create')
        ->assertHasNoFormErrors();

    $baris = ServicePart::firstOrFail();
    $baris->service->syncStock();

    expect($baris->quantity)->toBe(0.35)
        ->and($baris->subtotal)->toBe(45500.0)
        ->and($kawat->fresh()->stock)->toBe(6.15);
});

it('UNT-005: form servis menolak jumlah pecahan untuk suku cadang ber-satuan pcs', function () {
    $bearing = SparePart::factory()->create(['unit' => StockUnit::Pcs, 'stock' => 10]);

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['status' => 'in_progress', 'serviceParts' => [
            ['spare_part_id' => $bearing->id, 'quantity' => 1.5, 'unit_price' => 100000],
        ]]))
        ->call('create')
        ->assertHasFormErrors(['serviceParts.0.quantity']);

    expect(Service::count())->toBe(0);
});

it('UNT-006: jumlah ditampilkan gaya Indonesia dengan satuan', function () {
    expect(Qty::format(0.35, StockUnit::Kg))->toBe('0,35 kg')
        ->and(Qty::format(2, StockUnit::Pcs))->toBe('2 pcs')
        ->and(Qty::format(1250, 'pcs'))->toBe('1.250 pcs')
        ->and(Qty::format(6.5))->toBe('6,5');
});
