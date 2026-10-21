<?php

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use Livewire\Livewire;



it('PRC-001: harga jasa tampil bulat saat edit, tidak kali 100', function () {
    $this->actingAs(User::factory()->create());

    $type = ServiceType::create(['name' => 'Servis dinamo', 'default_price' => 65000]);

    $service = Service::create([
        'customer_name' => 'Budi',
        'phone' => '081234567890',
        'complaint' => 'Mati total',
        'scheduled_at' => now(),
        'status' => ServiceStatus::Pending,
        'booking_type' => BookingType::Workshop,
    ]);

    $service->items()->create([
        'service_type_id' => $type->id,
        'name' => $type->name,
        'price' => 65000,
    ]);

    Livewire::test(EditService::class, ['record' => $service->getKey()])
        ->assertFormSet(function (array $state) {
            expect(collect($state['items'])->first()['price'])->toEqual(65000);
        });
});