<?php

use App\Enums\ServiceStatus;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\TodaySchedule;
use App\Filament\Widgets\TodayStats;
use App\Models\Service;
use App\Models\SparePart;
use App\Models\User;
use Livewire\Livewire;

// Dasbor bagian "Hari ini" (20 Nov 2026, aturan 24). Kode modul: HRI. Tidak ikut filter periode.

beforeEach(fn () => $this->actingAs(User::factory()->create()));

test('HRI-001: jadwal hari ini hanya servis hari ini yang tidak batal', function () {
    $pagi = Service::factory()->create(['status' => ServiceStatus::Confirmed, 'scheduled_at' => now()->setTime(9, 0)]);
    $selesai = Service::factory()->create(['status' => ServiceStatus::Completed, 'scheduled_at' => now()->setTime(8, 0)]);
    $batal = Service::factory()->create(['status' => ServiceStatus::Cancelled, 'scheduled_at' => now()->setTime(10, 0)]);
    $besok = Service::factory()->create(['status' => ServiceStatus::Confirmed, 'scheduled_at' => now()->addDay()->setTime(9, 0)]);

    Livewire::test(TodaySchedule::class)
        ->assertCanSeeTableRecords([$selesai, $pagi], inOrder: true)
        ->assertCanNotSeeTableRecords([$batal, $besok]);
});

test('HRI-002: angka hari ini: menunggu konfirmasi, servis hari ini, stok menipis', function () {
    Service::factory()->create(['status' => ServiceStatus::Pending, 'scheduled_at' => now()->addDays(3)]);
    Service::factory()->create(['status' => ServiceStatus::Confirmed, 'scheduled_at' => now()->setTime(9, 0)]);
    Service::factory()->create(['status' => ServiceStatus::InProgress, 'scheduled_at' => now()->setTime(10, 0)]);
    SparePart::factory()->create(['stock' => 1, 'min_stock' => 3]);
    SparePart::factory()->create(['stock' => 10, 'min_stock' => 3]);

    $stats = collect((fn () => $this->getStats())->call(Livewire::test(TodayStats::class)->instance()))
        ->mapWithKeys(fn ($s) => [$s->getLabel() => $s->getValue()]);

    expect($stats['Menunggu konfirmasi'])->toBe('1')
        ->and($stats['Servis hari ini'])->toBe('2')
        ->and($stats['Stok menipis'])->toBe('1');
});

test('HRI-003: dasbor memuat bagian Hari ini', function () {
    Livewire::test(Dashboard::class)
        ->assertSeeLivewire(TodayStats::class)
        ->assertSeeLivewire(TodaySchedule::class)
        ->assertSee('Kinerja periode');
});
