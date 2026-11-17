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

it('FRM-002: HP 16 karakter diterima', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['customer_phone' => '0812345678901234']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::count())->toBe(1);
});

it('FRM-003: HP lebih dari 16 karakter ditolak', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['customer_phone' => '08123456789012345']))
        ->call('create')
        ->assertHasFormErrors(['customer_phone' => 'max']);

    expect(Service::count())->toBe(0);
});