<?php

namespace App\Support\Dashboard;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Menerjemahkan pilihan filter dashboard menjadi rentang tanggal.
 * Bulan ini = tanggal 1 sampai hari ini (bukan sampai akhir bulan).
 */
final class DashboardPeriod
{
    public const DEFAULT = 'this_month';

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
        $preset = $filters['preset'] ?? self::DEFAULT;

        [$from, $to] = match ($preset) {
            'today' => [$today, $today],
            'last7' => [$today->subDays(6), $today],
            'this_year' => [$today->startOfYear(), $today],
            'custom' => self::custom($filters ?? [], $today),
            default => [$today->startOfMonth(), $today],
        };

        return new ServiceMetrics($from, $to);
    }

        /** Teks untuk tombol periode: nama pilihan, atau rentang tanggal kalau pilih tanggal. */
    public static function label(?array $filters): string
    {
        $preset = $filters['preset'] ?? self::DEFAULT;

        if ($preset !== 'custom') {
            return self::options()[$preset] ?? self::options()[self::DEFAULT];
        }

        $metrics = self::metrics($filters);

        return $metrics->from()->format('d/m/Y').' - '.$metrics->to()->format('d/m/Y');
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private static function custom(array $filters, CarbonImmutable $today): array
    {
        $from = self::parse($filters['start_date'] ?? null);
        $to = self::parse($filters['end_date'] ?? null);

        if ($from === null || $to === null) {
            return [$today->startOfMonth(), $today];
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        // Batas 366 hari supaya grafik per hari tidak berat.
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
}