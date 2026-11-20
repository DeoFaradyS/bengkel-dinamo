<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/**
 * Grafik pendapatan (menggantikan grafik tren booking, 20 Nov 2026). Batang per hari, per minggu,
 * atau per bulan sesuai panjang periode. Dasar uang diterima, sama dengan Laporan Pendapatan.
 */
class RevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '320px';

    public function getHeading(): ?string
    {
        $unit = DashboardPeriod::metrics($this->pageFilters)->revenueSeries()['unit'];

        return 'Pendapatan per ' . ['day' => 'hari', 'week' => 'minggu', 'month' => 'bulan'][$unit];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $series = DashboardPeriod::metrics($this->pageFilters)->revenueSeries();

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $series['values'],
                    'backgroundColor' => '#3b82f6',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
