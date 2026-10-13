<?php

use App\Models\Service;
use App\Models\ServicePart;
use App\Models\SparePart;

it('INV-010: part sudah di-soft-delete, invoicePdf tetap jadi PDF', function () {
    $service = Service::factory()->completed()->create();
    $part = SparePart::factory()->create(['stock' => 10]);

    ServicePart::factory()->create([
        'service_id' => $service->id,
        'spare_part_id' => $part->id,
        'quantity' => 2,
        'unit_price' => 100000,
    ]);

    $part->delete();

    $pdf = $service->fresh()->invoicePdf();

    expect(substr($pdf, 0, 4))->toBe('%PDF');
});