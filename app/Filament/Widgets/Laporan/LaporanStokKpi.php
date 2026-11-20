<?php

namespace App\Filament\Widgets\Laporan;

use App\Support\Dashboard\DashboardPeriod;
use App\Support\Reports\StockMutation;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Kartu ringkasan Laporan Stok (aturan 31), tampilannya sama dengan kartu KPI dasbor. */
class LaporanStokKpi extends StatsOverviewWidget
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
        $m = DashboardPeriod::metrics($filters);
        $t = (new StockMutation($m->from(), $m->to()))->totals();

        return [
            ['key' => 'revenue', 'label' => 'Barang masuk', 'value' => $t['in'] . ' buah'],
            ['key' => 'completed', 'label' => 'Barang keluar', 'value' => $t['out'] . ' buah'],
            ['key' => 'bookings', 'label' => 'Penyesuaian', 'value' => ($t['adjustment'] > 0 ? '+' : '') . $t['adjustment'] . ' buah'],
            ['key' => 'low', 'label' => 'Hampir habis', 'value' => $t['low'] . ' part'],
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
