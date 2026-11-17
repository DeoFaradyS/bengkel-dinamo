<?php

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use Livewire\Livewire;

it('HPU-001: HP sama dengan servis aktif ditolak', function () {
    Service::factory()->create(['customer_phone' => '081234500001']);

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['customer_phone' => '081234500001']))
        ->call('create')
        ->assertHasFormErrors(['customer_phone' => 'unique']);

    expect(Service::count())->toBe(1);
});

it('HPU-002: HP sama dengan servis Selesai boleh', function () {
    Service::factory()->completed()->create(['customer_phone' => '081234500001']);

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['customer_phone' => '081234500001']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::count())->toBe(2);
});

it('HPU-003: HP sama dengan servis yang di-soft-delete boleh', function () {
    Service::factory()->create(['customer_phone' => '081234500001'])->delete();

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['customer_phone' => '081234500001']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::count())->toBe(1);
});

it('HPU-004: edit servis sendiri, HP tidak bentrok dengan dirinya', function () {
    $service = Service::factory()->create(['customer_phone' => '081234500001', 'source' => 'admin']);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['complaint' => 'Keluhan diubah'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->fresh()->complaint)->toBe('Keluhan diubah');
});

it('HPU-005: edit HP jadi nomor servis aktif lain ditolak', function () {
    Service::factory()->create(['customer_phone' => '081234500001']);
    $service = Service::factory()->create(['customer_phone' => '081234500002']);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['customer_phone' => '081234500001'])
        ->call('save')
        ->assertHasFormErrors(['customer_phone' => 'unique']);
});
it('HPU-006: +62 dianggap sama dengan 08, servis aktif bentrok ditolak', function () {
    Service::factory()->create(['customer_phone' => '081234500001']);

    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['customer_phone' => '+6281234500001']))
        ->call('create')
        ->assertHasFormErrors(['customer_phone' => 'unique']);

    expect(Service::count())->toBe(1);
});

it('HPU-007: HP disimpan dalam format 08...', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData(['customer_phone' => '+62 812 3450 0009']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::first()->customer_phone)->toBe('081234500009');
});