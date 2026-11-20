<?php

use App\Filament\Resources\Services\Pages\CreateService;
use App\Models\Service;
use Livewire\Livewire;

it('FRM-001: field wajib kosong ditolak', function (string $field) {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData([$field => null]))
        ->call('create')
        ->assertHasFormErrors([$field => 'required']);

    expect(Service::count())->toBe(0);
})->with([
    'nama pelanggan' => ['customer_name'],
    'no HP' => ['customer_phone'],
    'keluhan' => ['complaint'],
    'jadwal' => ['scheduled_at'],
]);

it('FRM-002: HP 14 digit (batas atas aturan 35) diterima', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['customer_phone' => '08123456789012']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::count())->toBe(1);
});

it('FRM-003: HP lebih dari 14 digit ditolak', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['customer_phone' => '081234567890123']))
        ->call('create')
        ->assertHasFormErrors(['customer_phone' => 'regex']);

    expect(Service::count())->toBe(0);
});