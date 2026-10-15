<?php

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Livewire\BookingForm;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function alamatData(array $override = []): array
{
    return array_merge([
        'customer_name' => 'Bu Rina',
        'phone' => '081234500020',
        'complaint' => 'Dinamo mati total',
        'scheduled_at' => now()->addDay(),
        'booking_type' => BookingType::HomeService->value,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
        'address' => 'depan Indomaret, pagar hijau',
    ], $override);
}

function alamatFormData(array $override = []): array
{
    return alamatData(array_merge([
        'status' => ServiceStatus::Confirmed->value,
        'items' => [],
        'serviceParts' => [],
    ], $override));
}

it('ADR-001: Home Service, alamat tersimpan', function () {
    $service = app(CreateBooking::class)->handle(alamatData());

    expect($service->fresh()->address)->toBe('depan Indomaret, pagar hijau');
});

it('ADR-002: Home Service tanpa alamat tetap diterima', function () {
    $service = app(CreateBooking::class)->handle(alamatData(['address' => '']));

    expect($service->fresh()->address)->toBeNull();
});

it('ADR-003: Ke Bengkel, alamat diabaikan', function () {
    $service = app(CreateBooking::class)->handle(alamatData([
        'booking_type' => BookingType::Workshop->value,
        'latitude' => null,
        'longitude' => null,
    ]));

    expect($service->fresh()->address)->toBeNull();
});

it('ADR-004: isi lonceng Home Service memuat alamat', function () {
    $admin = User::factory()->create();

    app(CreateBooking::class)->handle(alamatData());

    expect($admin->notifications()->first()->data['body'])
        ->toContain('depan Indomaret, pagar hijau');
});

it('ADR-005: form pelanggan Home Service tanpa alamat tetap terkirim', function () {
    Livewire::test(BookingForm::class)
        ->set('customer_name', 'Bu Rina')
        ->set('phone', '081234500021')
        ->set('complaint', 'Dinamo mati')
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('booking_type', BookingType::HomeService->value)
        ->set('latitude', -7.7174465)
        ->set('longitude', 113.0755426)
        ->call('submit')
        ->assertHasNoErrors();

    expect(Service::where('customer_name', 'Bu Rina')->first()->address)->toBeNull();
});

it('ADR-006: form pelanggan Home Service dengan alamat tersimpan', function () {
    Livewire::test(BookingForm::class)
        ->set('customer_name', 'Bu Rina')
        ->set('phone', '081234500022')
        ->set('complaint', 'Dinamo mati')
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('booking_type', BookingType::HomeService->value)
        ->set('latitude', -7.7174465)
        ->set('longitude', 113.0755426)
        ->set('address', 'gang kedua, rumah cat biru')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Service::where('customer_name', 'Bu Rina')->first()->address)
        ->toBe('gang kedua, rumah cat biru');
});

it('ADR-007: form admin, alamat tampil hanya untuk Home Service', function () {
    Livewire::test(CreateService::class)
        ->assertFormFieldHidden('address')
        ->fillForm(['booking_type' => BookingType::HomeService->value])
        ->assertFormFieldVisible('address');
});

it('ADR-008: form admin Home Service tanpa alamat tetap tersimpan', function () {
    Livewire::test(CreateService::class)
        ->fillForm(alamatFormData(['phone' => '081234500023', 'address' => null]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::where('phone', '081234500023')->first()->address)->toBeNull();
});

it('ADR-009: form admin Home Service dengan alamat tersimpan', function () {
    Livewire::test(CreateService::class)
        ->fillForm(alamatFormData(['phone' => '081234500024']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::where('phone', '081234500024')->first()->address)
        ->toBe('depan Indomaret, pagar hijau');
});

it('ADR-010: ubah ke Ke Bengkel, alamat dikosongkan', function () {
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
        'address' => 'depan Indomaret',
    ]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['booking_type' => BookingType::Workshop->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->fresh()->address)->toBeNull();
});

it('ADR-011: tabel tidak punya kolom alamat', function () {
    Livewire::test(ListServices::class)
        ->assertTableColumnDoesNotExist('address');
});