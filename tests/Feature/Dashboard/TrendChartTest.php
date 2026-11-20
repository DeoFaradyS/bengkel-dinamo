<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\RevenueChart;
use App\Models\User;
use Livewire\Livewire;

// Sejak 20 Nov 2026 grafik tren booking diganti grafik pendapatan (RevenueChart). ID tetap.
beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('TRD-001: grafik pendapatan tampil dengan judul per hari untuk bulan ini', function () {
    Livewire::test(RevenueChart::class)
        ->assertSee('Pendapatan per hari');
});

it('TRD-002: dashboard memuat grafik pendapatan', function () {
    Livewire::test(Dashboard::class)->assertSeeLivewire(RevenueChart::class);
});
