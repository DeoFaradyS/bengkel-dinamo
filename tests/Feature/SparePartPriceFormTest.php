<?php

use App\Filament\Resources\SpareParts\Pages\EditSparePart;
use App\Models\SparePart;
use App\Models\User;
use Livewire\Livewire;

it('PRC-002: harga part tampil bulat saat edit, tidak kali 100', function () {
    $this->actingAs(User::factory()->create());

    $part = SparePart::create([
        'code' => 'SP-901',
        'name' => 'Bendix',
        'stock' => 5,
        'price' => 65000,
    ]);

    Livewire::test(EditSparePart::class, ['record' => $part->getKey()])
        ->assertFormSet(function (array $state) {
            expect($state['price'])->toEqual(65000);
        });
});