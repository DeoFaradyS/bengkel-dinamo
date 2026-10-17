<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\TrendChart;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('TRD-001: grafik tampil dengan judul dan dua garis', function () {
    Livewire::test(TrendChart::class)
        ->assertSee('Tren Booking dan Servis Selesai')
        ->assertSee('Booking masuk')
        ->assertSee('Servis selesai');
});

it('TRD-002: dashboard memuat grafik tren', function () {
    Livewire::test(Dashboard::class)->assertSeeLivewire(TrendChart::class);
});