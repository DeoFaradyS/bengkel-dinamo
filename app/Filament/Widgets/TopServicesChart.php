<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Support\RawJs;

class TopServicesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;
    protected ?string $maxHeight = '320px';

    public function getHeading(): ?string
    {
        return 'Jasa Terlaris';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $top = DashboardPeriod::metrics($this->pageFilters)->topServices();

        return [
            'datasets' => [
                [
                    'label' => 'Dipakai (kali)',
                    'data' => array_values($top),
                    'backgroundColor' => '#3b82f6',
                ]
            ],
            'labels' => array_keys($top),
        ];
    }

    // Bar mendatar, sumbu angka bulat, tanpa legenda (cuma satu seri).
    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                    },
                    y: {
                        ticks: {
                            callback: function (value) {
                                const label = this.getLabelForValue(value);
                                const max = 22;

                                if (label.length <= max) {
                                    return label;
                                }

                                const lines = [''];

                                for (const word of label.split(' ')) {
                                    const last = lines.length - 1;
                                    const next = (lines[last] + ' ' + word).trim();

                                    if (next.length <= max) {
                                        lines[last] = next;
                                    } else {
                                        lines.push(word);
                                    }
                                }

                                if (lines.length > 2) {
                                    lines.length = 2;
                                    lines[1] = lines[1].slice(0, max - 1) + '…';
                                }

                                return lines;
                            },
                        },
                    },
                },
            }
        JS);
    }
}