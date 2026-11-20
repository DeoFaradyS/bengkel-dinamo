<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

// Filter Tanggal di tabel Servis (20 Nov 2026, aturan 22). Kode modul: TGL.
// Default Hari ini; bisa Besok, tanggal tertentu, atau semua tanggal. Tab Menunggu tidak ikut difilter.

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function servisTanggal($tanggal, ServiceStatus $status = ServiceStatus::Confirmed): Service
{
    return Service::factory()->create(['status' => $status, 'scheduled_at' => $tanggal]);
}

test('TGL-001: tabel dibuka, yang tampil hanya servis hari ini', function () {
    $hariIni = servisTanggal(now()->setTime(10, 0));
    $besok = servisTanggal(now()->addDay()->setTime(10, 0));
    $kemarin = servisTanggal(now()->subDay()->setTime(10, 0), ServiceStatus::Completed);

    Livewire::test(ListServices::class)
        ->assertCanSeeTableRecords([$hariIni])
        ->assertCanNotSeeTableRecords([$besok, $kemarin]);
});

test('TGL-002: pilihan Besok menampilkan servis besok saja', function () {
    $hariIni = servisTanggal(now()->setTime(10, 0));
    $besok = servisTanggal(now()->addDay()->setTime(10, 0));

    Livewire::test(ListServices::class)
        ->filterTable('tanggal', ['rentang' => 'tomorrow'])
        ->assertCanSeeTableRecords([$besok])
        ->assertCanNotSeeTableRecords([$hariIni]);
});

test('TGL-003: Pilih tanggal menampilkan servis di tanggal itu, termasuk lebih dari 14 hari lalu', function () {
    $lama = servisTanggal(now()->subDays(30)->setTime(9, 0), ServiceStatus::Completed);
    $hariIni = servisTanggal(now()->setTime(10, 0));

    Livewire::test(ListServices::class)
        ->filterTable('tanggal', ['rentang' => 'date', 'tanggal' => now()->subDays(30)->toDateString()])
        ->assertCanSeeTableRecords([$lama])
        ->assertCanNotSeeTableRecords([$hariIni]);
});

test('TGL-004: Semua tanggal menampilkan semua servis', function () {
    $hariIni = servisTanggal(now()->setTime(10, 0));
    $besok = servisTanggal(now()->addDay()->setTime(10, 0));

    Livewire::test(ListServices::class)
        ->filterTable('tanggal', ['rentang' => 'all'])
        ->assertCanSeeTableRecords([$hariIni, $besok]);
});

test('TGL-005: tab Menunggu tidak ikut difilter, booking menunggu untuk besok tetap tampil', function () {
    $menungguBesok = servisTanggal(now()->addDays(3)->setTime(10, 0), ServiceStatus::Pending);

    Livewire::test(ListServices::class)
        ->set('activeTab', ServiceStatus::Pending->value)
        ->assertCanSeeTableRecords([$menungguBesok]);
});
