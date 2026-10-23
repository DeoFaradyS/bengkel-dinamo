<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use Livewire\Livewire;
use App\Filament\Resources\Services\Pages\CreateService;
use Filament\Forms\Components\ToggleButtons;

// Perpindahan sah lewat form Edit (Completed sudah dicek di STK-008)
it('FLW-001: perpindahan status sah lolos', function (ServiceStatus $from, ServiceStatus $to) {
    $service = Service::factory()->create(['status' => $from]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['status' => $to->value, 'cancel_reason' => 'Alasan tes'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->fresh()->status)->toBe($to);
})->with([
    'pending ke confirmed' => [ServiceStatus::Pending, ServiceStatus::Confirmed],
    'pending ke rejected' => [ServiceStatus::Pending, ServiceStatus::Rejected],
    'pending ke cancelled' => [ServiceStatus::Pending, ServiceStatus::Cancelled],
    'confirmed ke in progress' => [ServiceStatus::Confirmed, ServiceStatus::InProgress],
    'confirmed ke cancelled' => [ServiceStatus::Confirmed, ServiceStatus::Cancelled],
    'in progress ke cancelled' => [ServiceStatus::InProgress, ServiceStatus::Cancelled],
]);

it('FLW-002: perpindahan status terlarang ditolak', function (ServiceStatus $from, ServiceStatus $to) {
    $service = Service::factory()->create(['status' => $from]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['status' => $to->value, 'cancel_reason' => 'Alasan tes'])
        ->call('save')
        ->assertHasFormErrors(['status']);

    expect($service->fresh()->status)->toBe($from);
})->with([
    'pending ke in progress' => [ServiceStatus::Pending, ServiceStatus::InProgress],
    'pending ke completed' => [ServiceStatus::Pending, ServiceStatus::Completed],
    'confirmed ke pending' => [ServiceStatus::Confirmed, ServiceStatus::Pending],
    'confirmed ke completed' => [ServiceStatus::Confirmed, ServiceStatus::Completed],
    'confirmed ke rejected' => [ServiceStatus::Confirmed, ServiceStatus::Rejected],
    'in progress ke pending' => [ServiceStatus::InProgress, ServiceStatus::Pending],
    'in progress ke confirmed' => [ServiceStatus::InProgress, ServiceStatus::Confirmed],
    'in progress ke rejected' => [ServiceStatus::InProgress, ServiceStatus::Rejected],
]);

it('FLW-003: servis final tidak bisa diedit', function (ServiceStatus $status) {
    $service = Service::factory()->create(['status' => $status]);

    expect(ServiceResource::canEdit($service))->toBeFalse();
})->with([
    'selesai' => [ServiceStatus::Completed],
    'dibatalkan' => [ServiceStatus::Cancelled],
    'ditolak' => [ServiceStatus::Rejected],
]);

it('FLW-004: servis aktif masih bisa diedit', function (ServiceStatus $status) {
    $service = Service::factory()->create(['status' => $status]);

    expect(ServiceResource::canEdit($service))->toBeTrue();
})->with([
    'menunggu' => [ServiceStatus::Pending],
    'dikonfirmasi' => [ServiceStatus::Confirmed],
    'dikerjakan' => [ServiceStatus::InProgress],
]);

it('FLW-005: form Edit cuma tampilkan status sekarang dan yang sah', function (ServiceStatus $from, array $expected) {
    $service = Service::factory()->create(['status' => $from]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->assertFormFieldExists('status', function (ToggleButtons $field) use ($expected) {
            return array_keys($field->getOptions()) === array_map(fn($s) => $s->value, $expected);
        });
})->with([
    'pending' => [ServiceStatus::Pending, [ServiceStatus::Pending, ServiceStatus::Confirmed, ServiceStatus::Rejected, ServiceStatus::Cancelled]],
    'confirmed' => [ServiceStatus::Confirmed, [ServiceStatus::Confirmed, ServiceStatus::InProgress, ServiceStatus::Cancelled]],
    'in progress' => [ServiceStatus::InProgress, [ServiceStatus::InProgress, ServiceStatus::Completed, ServiceStatus::Cancelled]],
]);

it('FLW-006: form Create menampilkan semua status kecuali Kedaluwarsa', function () {
    Livewire::test(CreateService::class)
        ->assertFormFieldExists('status', function (ToggleButtons $field) {
            $options = $field->getOptions();

            return count($options) === count(ServiceStatus::cases()) - 1
                && ! array_key_exists(ServiceStatus::Expired->value, $options);
        });
});