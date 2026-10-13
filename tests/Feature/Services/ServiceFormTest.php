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
    'no HP' => ['phone'],
    'keluhan' => ['complaint'],
    'jadwal' => ['scheduled_at'],
]);

it('FRM-002: HP 16 karakter diterima', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['phone' => '0812345678901234']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::count())->toBe(1);
});

it('FRM-003: HP lebih dari 16 karakter ditolak', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['phone' => '08123456789012345']))
        ->call('create')
        ->assertHasFormErrors(['phone' => 'max']);

    expect(Service::count())->toBe(0);
});