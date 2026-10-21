<?php

use App\Enums\StockMovementType;
use App\Models\SparePart;

it('STK-001: part baru dengan stok awal punya catatan stok awal', function () {
    $part = SparePart::factory()->create(['stock' => 10]);
    $row = $part->stockMovements()->first();

    expect($row->type)->toBe(StockMovementType::Opening)
        ->and($row->quantity)->toBe(10)
        ->and($row->stock_after)->toBe(10);
});

it('STK-002: part baru dengan stok 0 tidak punya catatan', function () {
    $part = SparePart::factory()->create(['stock' => 0]);

    expect($part->stockMovements()->count())->toBe(0);
});

it('STK-003: barang masuk menambah stok dan tercatat', function () {
    $part = SparePart::factory()->create(['stock' => 10]);

    $part->addStock(5, StockMovementType::Purchase, 'beli dari supplier');

    $row = $part->stockMovements()->latest('id')->first();

    expect($part->fresh()->stock)->toBe(15)
        ->and($row->type)->toBe(StockMovementType::Purchase)
        ->and($row->quantity)->toBe(5)
        ->and($row->stock_after)->toBe(15)
        ->and($row->note)->toBe('beli dari supplier');
});

it('STK-004: koreksi negatif mengurangi stok', function () {
    $part = SparePart::factory()->create(['stock' => 10]);

    $part->addStock(-2, StockMovementType::Correction, 'rusak di rak');

    expect($part->fresh()->stock)->toBe(8)
        ->and($part->stockMovements()->latest('id')->first()->quantity)->toBe(-2);
});

it('STK-005: koreksi di bawah nol ditolak, stok tidak berubah', function () {
    $part = SparePart::factory()->create(['stock' => 3]);

    expect(fn() => $part->addStock(-4, StockMovementType::Correction, 'salah hitung'))
        ->toThrow(RuntimeException::class);

    expect($part->fresh()->stock)->toBe(3)
        ->and($part->stockMovements()->count())->toBe(1);
});