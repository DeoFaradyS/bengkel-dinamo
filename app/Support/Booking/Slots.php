<?php

namespace App\Support\Booking;

use App\Enums\ServiceStatus;
use App\Models\Service;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class Slots
{
    /** Semua jam mulai slot pada satu hari, sesuai jam buka dan istirahat. */
    public static function forDate(CarbonInterface $date): Collection
    {
        $step = (int) config('booking.slot_minutes');
        $cursor = self::at($date, config('booking.open'));
        $close = self::at($date, config('booking.close'));
        $slots = collect();

        while ($cursor->copy()->addMinutes($step)->lte($close)) {
            if (!self::inBreak($cursor, $step)) {
                $slots->push($cursor->copy());
            }

            $cursor->addMinutes($step);
        }

        return $slots;
    }

    /** Apakah jam ini tepat di awal salah satu slot. */
    public static function isSlot(CarbonInterface $time): bool
    {
        return self::forDate($time)->contains(fn($s) => $s->equalTo($time));
    }

    /** Berapa servis aktif yang sudah menahan slot ini. */
    public static function taken(CarbonInterface $slot, ?int $ignoreServiceId = null): int
    {
        return Service::query()
            ->where('scheduled_at', $slot)
            ->whereIn('status', ServiceStatus::activeValues())
            ->when($ignoreServiceId, fn($q) => $q->where('id', '!=', $ignoreServiceId))
            ->count();
    }

    public static function isAvailable(CarbonInterface $slot, ?int $ignoreServiceId = null): bool
    {
        return self::taken($slot, $ignoreServiceId) < (int) config('booking.capacity');
    }

    /** Slot yang ditampilkan ke pelanggan. Terlalu dekat atau penuh tetap tampil, tapi tidak tersedia. */
    public static function bookable(CarbonInterface $date): Collection
    {
        $earliest = now()->addMinutes((int) config('booking.min_lead_minutes'));
        $latest = now()->addDays((int) config('booking.max_days_ahead'))->endOfDay();

        return self::forDate($date)
            ->filter(fn($s) => $s->gte(now()->startOfDay()) && $s->lte($latest))
            ->map(fn($s) => [
                'time' => $s,
                'available' => $s->gte($earliest) && self::isAvailable($s),
            ])
            ->values();
    }

    private static function at(CarbonInterface $date, string $hhmm): CarbonInterface
    {
        [$h, $m] = array_map('intval', explode(':', $hhmm));

        return $date->copy()->setTime($h, $m, 0);
    }

    private static function inBreak(CarbonInterface $start, int $step): bool
    {
        $end = $start->copy()->addMinutes($step);

        foreach (config('booking.breaks', []) as [$from, $to]) {
            if ($start->lt(self::at($start, $to)) && $end->gt(self::at($start, $from))) {
                return true;
            }
        }

        return false;
    }
}