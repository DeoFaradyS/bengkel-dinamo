<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;

it('NAV-001: tidak ada yang menunggu, badge kosong', function () {
    Service::factory()->create();

    expect(ServiceResource::getNavigationBadge())->toBeNull();
});

it('NAV-002: badge menghitung hanya status Menunggu Konfirmasi', function () {
    Service::factory()->count(2)->status(ServiceStatus::Pending)->create();
    Service::factory()->create();
    Service::factory()->completed()->create();

    expect(ServiceResource::getNavigationBadge())->toBe('2');
});

it('NAV-003: servis Menunggu yang di-soft-delete tidak dihitung', function () {
    Service::factory()->status(ServiceStatus::Pending)->create()->delete();

    expect(ServiceResource::getNavigationBadge())->toBeNull();
});