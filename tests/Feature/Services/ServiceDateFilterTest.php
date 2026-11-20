<?php

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

// Filter Tanggal di tabel Servis (20 Nov 2026, aturan 22). Kode modul: TGL.
// Kalender rentang (seperti filter dasbor), default Hari ini, ada pilihan cepat. Hapus filter = semua
// tanggal. Tab Menunggu tidak ikut difilter. Angka di tab ikut filter, kecuali Menunggu.

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function servisTanggal($tanggal, ServiceStatus $status = ServiceStatus::Confirmed): Service
{
    return Service::factory()->create(['status' => $status, 'scheduled_at' => $tanggal]);
}

function rentang($dari, $sampai = null): string
{
    return $dari->format('d/m/Y') . ' - ' . ($sampai ?? $dari)->format('d/m/Y');
}

test('TGL-001: tabel dibuka, yang tampil hanya servis hari ini', function () {
    $hariIni = servisTanggal(now()->setTime(10, 0));
    $besok = servisTanggal(now()->addDay()->setTime(10, 0));
    $kemarin = servisTanggal(now()->subDay()->setTime(10, 0), ServiceStatus::Completed);

    Livewire::test(ListServices::class)
        ->assertCanSeeTableRecords([$hariIni])
        ->assertCanNotSeeTableRecords([$besok, $kemarin]);
});

test('TGL-002: rentang besok menampilkan servis besok saja', function () {
    $hariIni = servisTanggal(now()->setTime(10, 0));
    $besok = servisTanggal(now()->addDay()->setTime(10, 0));

    Livewire::test(ListServices::class)
        ->filterTable('scheduled_at', ['scheduled_at' => rentang(now()->addDay())])
        ->assertCanSeeTableRecords([$besok])
        ->assertCanNotSeeTableRecords([$hariIni]);
});

test('TGL-003: tanggal lebih dari 14 hari lalu tetap bisa dipilih', function () {
    $lama = servisTanggal(now()->subDays(30)->setTime(9, 0), ServiceStatus::Completed);
    $hariIni = servisTanggal(now()->setTime(10, 0));

    Livewire::test(ListServices::class)
        ->filterTable('scheduled_at', ['scheduled_at' => rentang(now()->subDays(30))])
        ->assertCanSeeTableRecords([$lama])
        ->assertCanNotSeeTableRecords([$hariIni]);
});

test('TGL-004: filter dihapus, semua tanggal tampil', function () {
    $hariIni = servisTanggal(now()->setTime(10, 0));
    $besok = servisTanggal(now()->addDay()->setTime(10, 0));

    Livewire::test(ListServices::class)
        ->removeTableFilter('scheduled_at')
        ->assertCanSeeTableRecords([$hariIni, $besok]);
});

test('TGL-005: tab Menunggu tidak ikut difilter, booking menunggu untuk hari lain tetap tampil', function () {
    $menungguBesok = servisTanggal(now()->addDays(3)->setTime(10, 0), ServiceStatus::Pending);

    Livewire::test(ListServices::class)
        ->set('activeTab', ServiceStatus::Pending->value)
        ->assertCanSeeTableRecords([$menungguBesok]);
});

test('TGL-006: angka di tab ikut filter Tanggal, kecuali Menunggu yang menghitung semua tanggal', function () {
    servisTanggal(now()->setTime(10, 0));
    servisTanggal(now()->addDay()->setTime(10, 0));
    servisTanggal(now()->addDays(2)->setTime(10, 0), ServiceStatus::Pending);

    $tabs = Livewire::test(ListServices::class)->instance()->getTabs();

    expect($tabs['confirmed']->getBadge())->toEqual(1)
        ->and($tabs['all']->getBadge())->toEqual(1)
        ->and($tabs['pending']->getBadge())->toEqual(1);
});
