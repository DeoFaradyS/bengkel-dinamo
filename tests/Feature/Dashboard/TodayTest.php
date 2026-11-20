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

// HRI-002 (kartu Menunggu, Servis hari ini, Stok menipis) dihapus 20 Nov: kartunya dicabut. Jangan dipakai ulang.

test('HRI-003: dasbor memuat bagian Hari ini', function () {
    Livewire::test(Dashboard::class)
        ->assertSeeLivewire(TodaySchedule::class)
        ->assertSee('Kinerja periode');
});
