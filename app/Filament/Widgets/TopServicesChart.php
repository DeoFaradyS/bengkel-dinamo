<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopServicesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

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
            'datasets' => [[
                'label' => 'Dipakai (kali)',
                'data' => array_values($top),
                'backgroundColor' => '#3b82f6',
            ]],
            'labels' => array_keys($top),
        ];
    }

    // Bar mendatar, sumbu angka bulat, tanpa legenda (cuma satu seri).
    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}