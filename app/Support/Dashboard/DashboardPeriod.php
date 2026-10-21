<?php

namespace App\Support\Dashboard;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Menerjemahkan pilihan filter dashboard menjadi rentang tanggal.
 * Bulan ini = tanggal 1 sampai hari ini (bukan sampai akhir bulan).
 *
 * Filter dari date picker berbentuk ['range' => 'd/m/Y - d/m/Y'].
 * Bentuk lama (['preset' => ..., 'start_date' => ..., 'end_date' => ...]) tetap dikenali.
 */
final class DashboardPeriod
{
    public const DEFAULT = 'this_month';

    /** Preset yang tampil di sisi kiri date picker, urutan tetap. */
    private const PRESETS = ['today', 'last7', 'this_month', 'this_year'];

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            'today' => 'Hari ini',
            'last7' => '7 hari terakhir',
            'this_month' => 'Bulan ini',
            'this_year' => 'Tahun ini',
            'custom' => 'Pilih tanggal',
        ];
    }

    public static function metrics(?array $filters, ?CarbonInterface $now = null): ServiceMetrics
    {
        $today = CarbonImmutable::instance($now ?? now())->startOfDay();
        $mode = filled($filters['range'] ?? null) ? 'range' : ($filters['preset'] ?? self::DEFAULT);

        [$from, $to] = match ($mode) {
            'today' => [$today, $today],
            'last7' => [$today->subDays(6), $today],
            'this_year' => [$today->startOfYear(), $today],
            'custom' => self::custom($filters ?? [], $today),
            'range' => self::fromRange((string) $filters['range'], $today),
            default => [$today->startOfMonth(), $today],
        };

        return new ServiceMetrics($from, $to);
    }

    /** Teks untuk tombol periode: nama preset kalau rentangnya sama, kalau tidak tanggalnya. */
    public static function label(?array $filters, ?CarbonInterface $now = null): string
    {
        $mode = filled($filters['range'] ?? null) ? 'range' : ($filters['preset'] ?? self::DEFAULT);

        if (! in_array($mode, ['range', 'custom'], true)) {
            return self::options()[$mode] ?? self::options()[self::DEFAULT];
        }

        $metrics = self::metrics($filters, $now);

        foreach (self::PRESETS as $key) {
            $preset = self::metrics(['preset' => $key], $now);

            if ($preset->from()->isSameDay($metrics->from()) && $preset->to()->isSameDay($metrics->to())) {
                return self::options()[$key];
            }
        }

        return $metrics->from()->format('d/m/Y').' - '.$metrics->to()->format('d/m/Y');
    }

    /**
     * Preset untuk sisi kiri date picker: nama => [dari, sampai].
     *
     * @return array<string, array{0: \Carbon\Carbon, 1: \Carbon\Carbon}>
     */
    public static function ranges(?CarbonInterface $now = null): array
    {
        $ranges = [];

        foreach (self::PRESETS as $key) {
            $metrics = self::metrics(['preset' => $key], $now);
            $ranges[self::options()[$key]] = [$metrics->from()->toMutable(), $metrics->to()->toMutable()];
        }

        return $ranges;
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private static function fromRange(string $range, CarbonImmutable $today): array
    {
        $parts = preg_split('/\s+[-–]\s+/u', trim($range)) ?: [];

        $from = self::parseDmy($parts[0] ?? null);
        $to = self::parseDmy($parts[1] ?? $parts[0] ?? null);

        if ($from === null || $to === null) {
            return [$today->startOfMonth(), $today];
        }

        return self::normalize($from, $to);
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private static function custom(array $filters, CarbonImmutable $today): array
    {
        $from = self::parse($filters['start_date'] ?? null);
        $to = self::parse($filters['end_date'] ?? null);

        if ($from === null || $to === null) {
            return [$today->startOfMonth(), $today];
        }

        return self::normalize($from, $to);
    }

    /** Tukar kalau terbalik, potong maksimal 366 hari supaya grafik per hari tidak berat. */
    private static function normalize(CarbonImmutable $from, CarbonImmutable $to): array
    {
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        if ($from->lt($to->subDays(365))) {
            $from = $to->subDays(365);
        }

        return [$from, $to];
    }

    private static function parse(mixed $value): ?CarbonImmutable
    {
        if (blank($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /** Format d/m/Y ketat: tanggal tidak ada di kalender (misal 32/13/2026) ditolak. */
    private static function parseDmy(?string $value): ?CarbonImmutable
    {
        $value = trim((string) $value);

        try {
            $date = CarbonImmutable::createFromFormat('!d/m/Y', $value);
        } catch (\Throwable) {
            return null;
        }

        return $date && $date->format('d/m/Y') === $value ? $date : null;
    }
}