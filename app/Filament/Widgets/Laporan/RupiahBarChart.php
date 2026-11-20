<?php

namespace App\Filament\Widgets\Laporan;

use App\Support\Dashboard\DashboardPeriod;
use App\Support\Dashboard\ServiceMetrics;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/** Batang mendatar top 5 menurut Rupiah, untuk Laporan Pendapatan (aturan 30). */
abstract class RupiahBarChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '260px';

    protected static bool $isDiscovered = false;

    protected string $color = '#3b82f6';

    /** @return array<string, float> label => Rupiah */
    abstract protected function rows(ServiceMetrics $metrics): array;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = $this->rows(DashboardPeriod::metrics($this->pageFilters));

        return [
            'datasets' => [[
                'label' => 'Uang masuk',
                'data' => array_values($rows),
                'backgroundColor' => $this->color,
                'borderRadius' => 4,
            ]],
            'labels' => array_keys($rows),
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => ' Rp ' + ctx.parsed.x.toLocaleString('id-ID') } },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { callback: (v) => v >= 1000000 ? (v / 1000000).toLocaleString('id-ID') + ' jt' : (v >= 1000 ? (v / 1000).toLocaleString('id-ID') + ' rb' : v) },
                    },
                    y: {
                        ticks: {
                            callback: function (value) {
                                const label = this.getLabelForValue(value);
                                return label.length > 22 ? label.slice(0, 21) + '…' : label;
                            },
                        },
                    },
                },
            }
        JS);
    }
}
