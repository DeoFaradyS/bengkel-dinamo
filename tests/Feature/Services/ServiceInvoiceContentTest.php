<?php

use App\Models\Service;
use App\Models\ServicePart;
use App\Models\ServiceType;
use App\Models\SparePart;

it('INV-011: invoice memuat pelanggan, jasa, part, dan total', function () {
    $service = Service::factory()->completed()->create(['customer_name' => 'Pak Budi']);
    $type = ServiceType::factory()->create(['name' => 'Ganti bearing']);

    $service->items()->create([
        'service_type_id' => $type->id,
        'name' => $type->name,
        'price' => 50000,
    ]);

    $part = SparePart::factory()->create(['name' => 'Bearing 6203', 'stock' => 10]);

    ServicePart::factory()->create([
        'service_id' => $service->id,
        'spare_part_id' => $part->id,
        'quantity' => 2,
        'unit_price' => 100000,
    ]);

    $service->load(['items', 'serviceParts.sparePart']);

    $html = view('invoices.service', [
        'service' => $service,
        'invoiceNumber' => $service->invoiceNumber(),
    ])->render();

    expect($html)
        ->toContain('Pak Budi')
        ->toContain('Ganti bearing')
        ->toContain('Bearing 6203')
        ->toContain('Rp 250.000');
});