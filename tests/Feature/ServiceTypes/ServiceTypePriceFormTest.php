<?php

use App\Filament\Resources\ServiceTypes\Pages\EditServiceType;
use App\Models\ServiceType;
use App\Models\User;
use Livewire\Livewire;

it('PRC-003: harga acuan jenis servis tampil bulat saat edit, tidak kali 100', function () {
    $this->actingAs(User::factory()->create());

    $type = ServiceType::create([
        'name' => 'Servis dinamo',
        'default_price' => 65000,
    ]);

    Livewire::test(EditServiceType::class, ['record' => $type->getRouteKey()])
        ->assertFormSet(function (array $state) {
            expect($state['default_price'])->toEqual(65000);
        });
});