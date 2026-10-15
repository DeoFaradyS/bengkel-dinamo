<?php

use App\Enums\BookingType;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('FRM-009: Home Service, preview peta tampil', function () {
    Livewire::test(CreateService::class)
        ->fillForm(['booking_type' => BookingType::HomeService->value])
        ->assertSeeHtml('adminPinMap');
});

it('FRM-010: Ke Bengkel, preview peta tidak ada', function () {
    Livewire::test(CreateService::class)
        ->assertDontSeeHtml('adminPinMap');
});