<?php

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Resources\Services\Pages\CreateService;

it('ANT-001: tidak ada antrian, servis boleh mulai', function () {
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    expect(Service::startBlockerMessage($service, $service->scheduled_at))->toBeNull();
});

it('ANT-002: servis confirmed dengan jam lebih awal menghalangi', function () {
    Service::factory()->create([
        'customer_name' => 'Budi',
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 09:00:00',
    ]);
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    expect(Service::startBlockerMessage($service, $service->scheduled_at))
        ->toBe('Servis atas nama Budi lebih dulu antre.');
});

it('ANT-003: servis confirmed dengan jam lebih akhir tidak menghalangi', function () {
    Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 11:00:00',
    ]);
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    expect(Service::startBlockerMessage($service, $service->scheduled_at))->toBeNull();
});

it('ANT-004: booking pending tidak menghalangi', function () {
    Service::factory()->create([
        'status' => ServiceStatus::Pending,
        'scheduled_at' => '2026-11-03 09:00:00',
    ]);
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    expect(Service::startBlockerMessage($service, $service->scheduled_at))->toBeNull();
});

it('ANT-005: servis cancelled tidak menghalangi', function () {
    Service::factory()->create([
        'status' => ServiceStatus::Cancelled,
        'scheduled_at' => '2026-11-03 09:00:00',
    ]);
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    expect(Service::startBlockerMessage($service, $service->scheduled_at))->toBeNull();
});

it('ANT-006: masih ada servis in_progress menghalangi', function () {
    Service::factory()->create([
        'status' => ServiceStatus::InProgress,
        'scheduled_at' => '2026-11-03 12:00:00',
    ]);
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    expect(Service::startBlockerMessage($service, $service->scheduled_at))
        ->toBe('Masih ada servis yang sedang dikerjakan.');
});

it('ANT-007: jam sama, yang dibuat lebih dulu antre lebih dulu', function () {
    $first = Service::factory()->create([
        'customer_name' => 'Pertama',
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
        'created_at' => '2026-11-01 08:00:00',
    ]);
    $second = Service::factory()->create([
        'customer_name' => 'Kedua',
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
        'created_at' => '2026-11-01 09:00:00',
    ]);

    expect(Service::startBlockerMessage($first, $first->scheduled_at))->toBeNull()
        ->and(Service::startBlockerMessage($second, $second->scheduled_at))
        ->toBe('Servis atas nama Pertama lebih dulu antre.');
});

it('ANT-008: servis baru (tanpa record) menunggu yang jamnya sama atau lebih awal', function () {
    Service::factory()->create([
        'customer_name' => 'Budi',
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    expect(Service::startBlockerMessage(null, '2026-11-03 10:00:00'))
        ->toBe('Servis atas nama Budi lebih dulu antre.')
        ->and(Service::startBlockerMessage(null, '2026-11-03 09:00:00'))->toBeNull();
});

// Sejak 20 Nov 2026 jadwal lebih awal tidak mengunci lagi: yang muncul konfirmasi
// "Kerjakan servis ini duluan?" (confirmQueueSkip), bukan penghalang queueBlocked.
it('ANT-009: jadwal lebih awal belum dikerjakan, simpan ditahan dulu dengan konfirmasi', function () {
    $this->actingAs(User::factory()->create());

    Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 09:00:00',
    ]);
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    Livewire::test(EditService::class, ['record' => $service->getKey()])
        ->fillForm(['status' => ServiceStatus::InProgress->value])
        ->call('save')
        ->assertActionMounted('confirmQueueSkip');

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
});

it('ANT-010: simpan berhasil kalau sudah giliran', function () {
    $this->actingAs(User::factory()->create());

    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    Livewire::test(EditService::class, ['record' => $service->getKey()])
        ->fillForm(['status' => ServiceStatus::InProgress->value])
        ->call('save');

    expect($service->fresh()->status)->toBe(ServiceStatus::InProgress);
});

it('ANT-011: simpan servis baru tidak menyimpan baris jasa dan part kosong', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateService::class)
        ->fillForm([
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'complaint' => 'Dinamo tidak mau engkol',
            'scheduled_at' => '2026-11-03 10:00:00',
            'booking_type' => 'workshop',
            'status' => ServiceStatus::Confirmed->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $service = Service::where('customer_name', 'Budi')->first();

    expect($service)->not->toBeNull()
        ->and($service->items()->count())->toBe(0)
        ->and($service->serviceParts()->count())->toBe(0);
});

it('ANT-012: modal muncul kalau masih ada servis yang sedang dikerjakan', function () {
    $this->actingAs(User::factory()->create());

    Service::factory()->create([
        'status' => ServiceStatus::InProgress,
        'scheduled_at' => '2026-11-03 12:00:00',
    ]);
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    Livewire::test(EditService::class, ['record' => $service->getKey()])
        ->fillForm(['status' => ServiceStatus::InProgress->value])
        ->call('save')
        ->assertActionMounted('queueBlocked');

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
});

/* ------------------------------------------------------------------ */
/* ANT: lewati jadwal lebih awal (20 Nov 2026)                         */
/* ------------------------------------------------------------------ */

it('ANT-013: konfirmasi "Ya, kerjakan", servis jam lebih akhir tetap bisa dikerjakan duluan', function () {
    $this->actingAs(User::factory()->create());

    $awal = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 09:00:00',
    ]);
    $service = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => '2026-11-03 10:00:00',
    ]);

    Livewire::test(EditService::class, ['record' => $service->getKey()])
        ->fillForm(['status' => ServiceStatus::InProgress->value])
        ->call('save')
        ->assertActionMounted('confirmQueueSkip')
        ->callMountedAction();

    expect($service->fresh()->status)->toBe(ServiceStatus::InProgress)
        ->and($awal->fresh()->status)->toBe(ServiceStatus::Confirmed);
});

it('ANT-014: servis yang sedang dikerjakan tetap menghalangi, tanpa pilihan lewati', function () {
    expect(Service::runningServiceMessage(null))->toBeNull();

    Service::factory()->create(['status' => ServiceStatus::InProgress]);

    expect(Service::runningServiceMessage(null))->toBe('Masih ada servis yang sedang dikerjakan.');
});

/* ------------------------------------------------------------------ */
/* ANT: nomor antrean hari ini (aturan 22)                             */
/* ------------------------------------------------------------------ */

function servisHariIni(string $jam, ServiceStatus $status, array $o = []): Service
{
    return Service::factory()->create(array_merge([
        'status' => $status,
        'scheduled_at' => now()->setTimeFromTimeString($jam),
    ], $o));
}

it('ANT-015: nomor antrean mengikuti urutan jam hari ini', function () {
    $c = servisHariIni('13:00', ServiceStatus::Confirmed);
    $a = servisHariIni('09:00', ServiceStatus::Confirmed);
    $b = servisHariIni('10:00', ServiceStatus::Confirmed);

    expect($a->queueInfo()['number'])->toBe(1)
        ->and($b->queueInfo()['number'])->toBe(2)
        ->and($c->queueInfo()['number'])->toBe(3);
});

it('ANT-016: antrean 1 selesai, antrean 2 tetap nomor 2 dan tinggal 0', function () {
    $a = servisHariIni('09:00', ServiceStatus::Confirmed);
    $b = servisHariIni('10:00', ServiceStatus::Confirmed);

    $a->forceFill(['status' => ServiceStatus::Completed])->save();

    expect($b->queueInfo())->toBe(['number' => 2, 'current' => null, 'ahead' => 0]);
});

it('ANT-017: yang batal dan yang masih Menunggu tidak ikut dihitung', function () {
    servisHariIni('08:00', ServiceStatus::Cancelled);
    servisHariIni('09:00', ServiceStatus::Pending);
    $b = servisHariIni('10:00', ServiceStatus::Confirmed);

    expect($b->queueInfo()['number'])->toBe(1);
});

it('ANT-018: info antrean memuat nomor yang sedang dikerjakan dan sisa antrean di depan', function () {
    servisHariIni('09:00', ServiceStatus::InProgress);
    servisHariIni('10:00', ServiceStatus::Confirmed);
    $c = servisHariIni('11:00', ServiceStatus::Confirmed);

    expect($c->queueInfo())->toBe(['number' => 3, 'current' => 1, 'ahead' => 2]);
});

it('ANT-019: servis yang jadwalnya bukan hari ini tidak punya nomor antrean', function () {
    $besok = Service::factory()->create([
        'status' => ServiceStatus::Confirmed,
        'scheduled_at' => now()->addDay()->setTime(9, 0),
    ]);

    expect($besok->queueInfo())->toBeNull();
});
