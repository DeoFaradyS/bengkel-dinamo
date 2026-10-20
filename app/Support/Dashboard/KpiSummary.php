<?php

namespace App\Support\Dashboard;

/**
 * Menyusun kartu KPI dashboard beserta perbandingan dengan periode sebelumnya.
 */
final class KpiSummary
{
    /**
     * @return list<array{key: string, label: string, value: string, description: string, trend: ?string, positive: ?bool}>
     */
        public static function make(ServiceMetrics $current): array
    {
        $previous = $current->previous();

        return [
            self::card(
                'revenue', 'Pendapatan',
                self::rupiah($current->revenue()),
                self::percentChange($current->revenue(), $previous->revenue()),
                'persen', true,
            ),
            self::card(
                'completed', 'Servis selesai',
                (string) $current->completedCount(),
                self::percentChange($current->completedCount(), $previous->completedCount()),
                'persen', true,
            ),
            self::card(
                'bookings', 'Booking masuk',
                (string) $current->bookingsCount(),
                self::percentChange($current->bookingsCount(), $previous->bookingsCount()),
                'persen', true,
            ),
            [
                'key' => 'pending',
                'label' => 'Menunggu konfirmasi',
                'value' => (string) ServiceMetrics::pendingNow(),
                'description' => 'Saat ini, tidak ikut filter',
                'trend' => null,
                'positive' => null,
            ],
        ];
    }

    /** null = tidak ada pembanding (periode lalu kosong, sekarang ada isi). */
    private static function percentChange(float|int $current, float|int $previous): ?float
    {
        if ($previous <= 0) {
            return $current <= 0 ? 0.0 : null;
        }

        return ($current - $previous) / $previous * 100;
    }

    private static function card(string $key, string $label, string $value, ?float $change, string $unit, bool $higherIsBetter): array
    {
        if ($change === null) {
            return [
                'key' => $key, 'label' => $label, 'value' => $value,
                'description' => null, 'trend' => null, 'positive' => null,
            ];
        }

        if (abs($change) < 0.05) {
            return [
                'key' => $key, 'label' => $label, 'value' => $value,
                'description' => 'Sama dengan periode lalu', 'trend' => 'flat', 'positive' => null,
            ];
        }

        $up = $change > 0;

        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'description' => ($up ? 'Naik ' : 'Turun ')
                .self::number(abs($change))
                .($unit === 'poin' ? ' poin' : '%')
                .' dari periode lalu',
            'trend' => $up ? 'up' : 'down',
            'positive' => $up === $higherIsBetter,
        ];
    }

    private static function rupiah(float $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    private static function number(float $value): string
    {
        $text = number_format($value, 1, ',', '.');

        return str_ends_with($text, ',0') ? substr($text, 0, -2) : $text;
    }
}