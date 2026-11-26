<?php

use App\Actions\MarkServicePaid;
use App\Enums\ServiceStatus;
use App\Filament\Resources\SpareParts\Pages\CreateSparePart;
use App\Models\Service;
use App\Models\ServicePart;
use App\Models\SparePart;
use Livewire\Livewire;

// Garansi suku cadang (26 Nov 2026, aturan 38). Kode modul: GRN.
// Masa garansi disalin ke baris servis; berlaku sejak servis lunas sampai lunas + masa garansi.

function grnPakai(?int $hari, array $servis = []): ServicePart
{
    $part = SparePart::factory()->create(['name' => 'Bearing 6203', 'warranty_days' => $hari]);
    $s = Service::factory()->create(array_merge(['status' => ServiceStatus::InProgress], $servis));

    return $s->serviceParts()->create(['spare_part_id' => $part->id, 'quantity' => 1]);
}

function grnInvoice(Service $s): string
{
    $s->load(['items', 'serviceParts.sparePart']);

    return view('invoices.service', ['service' => $s, 'invoiceNumber' => $s->invoiceNumber()])->render();
}

it('GRN-001: masa garansi disalin ke servis, mengubah master tidak mengubah servis lama', function () {
    $baris = grnPakai(30);

    $baris->sparePart->update(['warranty_days' => 90]);

    expect($baris->fresh()->warranty_days)->toBe(30);
});

it('GRN-002: garansi tidak bisa diisi manual dari baris servis, dan ikut berganti kalau suku cadang diganti', function () {
    $baris = grnPakai(30);
    $lain = SparePart::factory()->create(['warranty_days' => null]);

    $baris->forceFill(['warranty_days' => 999])->save();
    expect($baris->fresh()->warranty_days)->toBe(30);

    $baris->update(['spare_part_id' => $lain->id]);
    expect($baris->fresh()->warranty_days)->toBeNull();
});

it('GRN-003: lunas 26 Nov dengan garansi 30 hari, berlaku sampai akhir 26 Des', function () {
    $this->travelTo('2026-11-26 15:00:00');
    $baris = grnPakai(30, ['status' => ServiceStatus::Completed]);
    app(MarkServicePaid::class)->handle($baris->service);

    $baris = $baris->fresh();

    expect($baris->warrantyUntil()->toDateTimeString())->toBe('2026-12-26 23:59:59')
        ->and($baris->warrantyLabel())->toBe('30 hari (s/d 26 Des 2026)');

    $this->travelTo('2026-12-26 23:00:00');
    expect($baris->fresh()->warrantyActive())->toBeTrue();

    $this->travelTo('2026-12-27 00:00:01');
    expect($baris->fresh()->warrantyActive())->toBeFalse();
});

it('GRN-004: servis selesai tapi belum lunas, garansi belum berjalan', function () {
    $baris = grnPakai(30, ['status' => ServiceStatus::Completed]);

    expect($baris->warrantyUntil())->toBeNull()
        ->and($baris->warrantyActive())->toBeFalse()
        ->and($baris->warrantyLabel())->toBe('30 hari (berlaku setelah lunas)');
});

it('GRN-005: suku cadang tanpa garansi (kosong atau 0) tertulis Tanpa garansi', function (?int $hari) {
    $baris = grnPakai($hari, ['status' => ServiceStatus::Completed, 'paid_at' => now()]);

    expect($baris->warranty_days)->toBeNull()
        ->and($baris->warrantyUntil())->toBeNull()
        ->and($baris->warrantyLabel())->toBe('Tanpa garansi');
})->with(['kosong' => null, 'nol' => 0]);

it('GRN-006: invoice memuat garansi per barang, ketentuan hanya muncul kalau ada barang bergaransi', function () {
    $ada = grnPakai(30, ['status' => ServiceStatus::Completed]);
    $tanpa = grnPakai(null, ['status' => ServiceStatus::Completed]);

    expect(grnInvoice($ada->service))->toContain('30 hari (berlaku setelah lunas)')->toContain('Ketentuan Garansi')
        ->and(grnInvoice($tanpa->service))->toContain('Tanpa garansi')->not->toContain('Ketentuan Garansi');
});

it('GRN-007: servis dibatalkan, garansi tertulis tidak berlaku', function () {
    $baris = grnPakai(30, ['status' => ServiceStatus::Cancelled]);

    expect($baris->warrantyLabel())->toBe('-');
});

it('GRN-008: form suku cadang menyimpan garansi, angka di luar 1..3650 atau pecahan ditolak', function () {
    $this->actingAs(\App\Models\User::factory()->owner()->create());
    $isi = ['code' => 'BR-01', 'name' => 'Bearing', 'price' => 50000, 'unit' => 'pcs', 'stock' => 5, 'min_stock' => 1];

    foreach ([0, 3651, 1.5] as $salah) {
        Livewire::test(CreateSparePart::class)
            ->fillForm($isi + ['warranty_days' => $salah])
            ->call('create')
            ->assertHasFormErrors(['warranty_days']);
    }

    Livewire::test(CreateSparePart::class)
        ->fillForm($isi + ['warranty_days' => 30])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SparePart::where('code', 'BR-01')->value('warranty_days'))->toBe(30);
});
