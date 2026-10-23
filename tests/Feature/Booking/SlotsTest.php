<?php

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Support\Booking\Slots;
use Illuminate\Support\Carbon;

it('SLT-001: sehari ada 9 slot, 07.00 sampai 16.00, tanpa jam istirahat', function () {
    $slots = Slots::forDate(Carbon::parse('2026-10-26'));

    expect($slots)->toHaveCount(9)
        ->and($slots->first()->format('H:i'))->toBe('07:00')
        ->and($slots->last()->format('H:i'))->toBe('16:00')
        ->and($slots->map->format('H:i')->contains('12:00'))->toBeFalse();
});

it('SLT-002: jam di luar slot ditolak', function (string $time) {
    expect(Slots::isSlot(Carbon::parse("2026-10-26 {$time}")))->toBeFalse();
})->with(['istirahat' => '12:00', 'bukan awal jam' => '09:30', 'sebelum buka' => '06:00', 'jam tutup' => '17:00']);

it('SLT-003: jam awal slot sah', function (string $time) {
    expect(Slots::isSlot(Carbon::parse("2026-10-26 {$time}")))->toBeTrue();
})->with(['07.00' => '07:00', '11.00' => '11:00', '13.00' => '13:00', '16.00' => '16:00']);

it('SLT-004: servis aktif menahan slot, servis final tidak', function (ServiceStatus $status, bool $holds) {
    $slot = Carbon::parse('2026-10-26 09:00');
    Service::factory()->create(['scheduled_at' => $slot, 'status' => $status]);

    expect(Slots::isAvailable($slot))->toBe(! $holds);
})->with([
    'menunggu' => [ServiceStatus::Pending, true],
    'dikonfirmasi' => [ServiceStatus::Confirmed, true],
    'dikerjakan' => [ServiceStatus::InProgress, true],
    'selesai' => [ServiceStatus::Completed, false],
    'dibatalkan' => [ServiceStatus::Cancelled, false],
    'ditolak' => [ServiceStatus::Rejected, false],
]);

it('SLT-005: servis yang sedang diedit tidak menghalangi slotnya sendiri', function () {
    $slot = Carbon::parse('2026-10-26 09:00');
    $service = Service::factory()->create(['scheduled_at' => $slot, 'status' => ServiceStatus::Pending]);

    expect(Slots::isAvailable($slot))->toBeFalse()
        ->and(Slots::isAvailable($slot, $service->id))->toBeTrue();
});

it('SLT-006: slot yang tampil ke pelanggan menghormati jarak minimal dan batas hari', function () {
    config(['booking.min_lead_minutes' => 30]);
    $this->travelTo(Carbon::parse('2026-10-23 10:30'));

    // Semua 9 slot hari ini tampil. Yang tersedia: 11.00 (tepat 30 menit lagi), 13, 14, 15, 16.
    $today = Slots::bookable(now());
    expect($today)->toHaveCount(9);

    $available = $today->filter(fn ($s) => $s['available'])
        ->map(fn ($s) => $s['time']->format('H:i'))
        ->values()
        ->all();
    expect($available)->toBe(['11:00', '13:00', '14:00', '15:00', '16:00']);

    // Lewat 14 hari: tidak ada slot.
    expect(Slots::bookable(now()->addDays(15)))->toBeEmpty();
});

it('SLT-007: slot penuh tetap tampil tapi ditandai tidak tersedia', function () {
    $this->travelTo(Carbon::parse('2026-10-23 06:00'));
    Service::factory()->create(['scheduled_at' => Carbon::parse('2026-10-23 09:00'), 'status' => ServiceStatus::Pending]);

    $slot = Slots::bookable(now())->first(fn ($s) => $s['time']->format('H:i') === '09:00');

    expect($slot['available'])->toBeFalse();
});

it('SLT-008: slot kurang dari 30 menit lagi tetap tampil tapi tidak tersedia', function () {
    config(['booking.min_lead_minutes' => 30]);
    $slot11 = fn () => Slots::bookable(now())->first(fn ($s) => $s['time']->format('H:i') === '11:00');

    // 10.30: slot 11.00 tepat 30 menit lagi, tersedia.
    $this->travelTo(Carbon::parse('2026-10-23 10:30'));
    expect($slot11()['available'])->toBeTrue();

    // 10.31: slot 11.00 tinggal 29 menit, tampil tapi tidak tersedia.
    $this->travelTo(Carbon::parse('2026-10-23 10:31'));
    expect($slot11())->not->toBeNull()
        ->and($slot11()['available'])->toBeFalse();
});