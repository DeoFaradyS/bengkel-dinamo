<?php

namespace App\Filament\Widgets\Laporan;

use App\Support\Dashboard\DashboardPeriod;
use App\Support\Dashboard\ServiceMetrics;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Kartu ringkasan Laporan Pendapatan (aturan 30), tampilannya sama dengan kartu KPI dasbor.
 * Uang masuk, servis lunas, dan rata-rata ikut periode; piutang semua tanggal.
 */
class LaporanKpi extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    public function getColumns(): int
    {
        return 4;
    }

    /** @return list<array{key: string, label: string, value: string}> */
    public static function cards(?array $filters): array
    {
        $rupiah = fn(float $n) => 'Rp ' . number_format($n, 0, ',', '.');
        $s = DashboardPeriod::metrics($filters)->revenueBreakdown();
        $u = ServiceMetrics::unpaidNow();

        return [
            ['key' => 'revenue', 'label' => 'Uang masuk', 'value' => $rupiah($s['total'])],
            ['key' => 'completed', 'label' => 'Servis lunas', 'value' => (string) $s['count']],
            ['key' => 'bookings', 'label' => 'Rata-rata per servis', 'value' => $rupiah($s['count'] > 0 ? $s['total'] / $s['count'] : 0)],
            ['key' => 'unpaid', 'label' => 'Piutang (' . $u['count'] . ' servis)', 'value' => $rupiah($u['total'])],
        ];
    }

    protected function getStats(): array
    {
        return collect(self::cards($this->pageFilters))
            ->map(fn(array $c) => Stat::make($c['label'], $c['value'])
                ->extraAttributes(['class' => 'kpi-stat kpi-' . $c['key']]))
            ->all();
    }
}
