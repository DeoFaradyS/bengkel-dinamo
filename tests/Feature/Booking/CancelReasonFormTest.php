<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function alasanData(array $override = []): array
{
    return array_merge([
        'customer_name' => 'Pak Tono',
        'customer_phone' => '081234500030',
        'complaint' => 'Dinamo rusak berat',
        'scheduled_at' => now()->addDay(),
        'status' => ServiceStatus::Rejected->value,
        'items' => [],
        'serviceParts' => [],
    ], $override);
}

it('RSN-001: status aktif, isian alasan tersembunyi', function () {
    Livewire::test(CreateService::class)
        ->fillForm(['status' => ServiceStatus::Confirmed->value])
        ->assertFormFieldHidden('cancel_reason');
});

it('RSN-002: status Ditolak atau Dibatalkan, isian alasan tampil', function () {
    Livewire::test(CreateService::class)
        ->fillForm(['status' => ServiceStatus::Rejected->value])
        ->assertFormFieldVisible('cancel_reason')
        ->fillForm(['status' => ServiceStatus::Cancelled->value])
        ->assertFormFieldVisible('cancel_reason');
});

it('RSN-003: Ditolak tanpa alasan ditolak form', function () {
    Livewire::test(CreateService::class)
        ->fillForm(alasanData())
        ->call('create')
        ->assertHasFormErrors(['cancel_reason' => 'required']);
});

it('RSN-004: Dibatalkan tanpa alasan ditolak form', function () {
    Livewire::test(CreateService::class)
        ->fillForm(alasanData(['status' => ServiceStatus::Cancelled->value]))
        ->call('create')
        ->assertHasFormErrors(['cancel_reason' => 'required']);
});

// Sejak 17 Nov 2026 servis tidak bisa dibuat langsung Ditolak, jadi alasan diisi lewat ubah.
it('RSN-005: Ditolak dengan alasan tersimpan', function () {
    $service = Service::factory()->create([
        'customer_phone' => '081234500030',
        'status' => ServiceStatus::Pending,
    ]);

    Livewire::test(\App\Filament\Resources\Services\Pages\EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['status' => ServiceStatus::Rejected->value, 'cancel_reason' => 'Biaya melebihi harga unit baru'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->fresh()->cancel_reason)->toBe('Biaya melebihi harga unit baru');
});