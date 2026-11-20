<?php

namespace App\Filament\Widgets\Laporan;

use App\Support\Dashboard\DashboardPeriod;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/** Donat sumber pendapatan: jasa, spare part, transport, dalam Rupiah (aturan 30). */
class RevenueSourceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '220px';

    protected static bool $isDiscovered = false;

    public function getHeading(): ?string
    {
        return 'Sumber pendapatan';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $s = DashboardPeriod::metrics($this->pageFilters)->revenueBreakdown();

        return [
            'datasets' => [[
                'data' => [$s['jobs'], $s['parts'], $s['transport']],
                'backgroundColor' => ['#3b82f6', '#22c55e', '#f59e0b'],
            ]],
            'labels' => ['Jasa', 'Spare part', 'Transport'],
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16 } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                                return ' Rp ' + ctx.parsed.toLocaleString('id-ID') + ' (' + pct + '%)';
                            },
                        },
                    },
                },
                scales: { x: { display: false }, y: { display: false } },
            }
        JS);
    }
}
