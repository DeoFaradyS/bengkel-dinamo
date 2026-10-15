<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function formTipeData(array $override = []): array
{
    return array_merge([
        'customer_name' => 'Pak Joko',
        'phone' => '081234500010',
        'complaint' => 'Dinamo bunyi kasar',
        'scheduled_at' => now()->addDay(),
        'status' => ServiceStatus::Confirmed->value,
        'items' => [],
        'serviceParts' => [],
    ], $override);
}

it('FRM-001: form punya pilihan tipe layanan', function () {
    Livewire::test(CreateService::class)
        ->assertFormFieldExists('booking_type');
});

it('FRM-002: Ke Bengkel, isian koordinat tersembunyi', function () {
    Livewire::test(CreateService::class)
        ->assertFormFieldHidden('latitude')
        ->assertFormFieldHidden('longitude');
});

it('FRM-003: Home Service, isian koordinat tampil', function () {
    Livewire::test(CreateService::class)
        ->fillForm(['booking_type' => BookingType::HomeService->value])
        ->assertFormFieldVisible('latitude')
        ->assertFormFieldVisible('longitude');
});

it('FRM-004: Home Service tanpa koordinat ditolak', function () {
    Livewire::test(CreateService::class)
        ->fillForm(formTipeData(['booking_type' => BookingType::HomeService->value]))
        ->call('create')
        ->assertHasFormErrors(['latitude' => 'required', 'longitude' => 'required']);
});

it('FRM-005: koordinat di luar rentang ditolak', function () {
    Livewire::test(CreateService::class)
        ->fillForm(formTipeData([
            'booking_type' => BookingType::HomeService->value,
            'latitude' => 95,
            'longitude' => 200,
        ]))
        ->call('create')
        ->assertHasFormErrors(['latitude' => 'max', 'longitude' => 'max']);
});

it('FRM-006: Home Service dengan koordinat tersimpan', function () {
    Livewire::test(CreateService::class)
        ->fillForm(formTipeData([
            'booking_type' => BookingType::HomeService->value,
            'latitude' => -7.7174465,
            'longitude' => 113.0755426,
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    $service = Service::where('phone', '081234500010')->first();

    expect($service->booking_type)->toBe(BookingType::HomeService)
        ->and($service->latitude)->toEqual(-7.7174465)
        ->and($service->longitude)->toEqual(113.0755426);
});

it('FRM-007: Ke Bengkel tersimpan tanpa koordinat', function () {
    Livewire::test(CreateService::class)
        ->fillForm(formTipeData())
        ->call('create')
        ->assertHasNoFormErrors();

    $service = Service::where('phone', '081234500010')->first();

    expect($service->booking_type)->toBe(BookingType::Workshop)
        ->and($service->latitude)->toBeNull()
        ->and($service->longitude)->toBeNull();
});

it('FRM-008: ubah Home Service jadi Ke Bengkel, koordinat dikosongkan', function () {
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
    ]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['booking_type' => BookingType::Workshop->value])
        ->call('save')
        ->assertHasNoFormErrors();

    $service->refresh();

    expect($service->booking_type)->toBe(BookingType::Workshop)
        ->and($service->latitude)->toBeNull()
        ->and($service->longitude)->toBeNull();
});