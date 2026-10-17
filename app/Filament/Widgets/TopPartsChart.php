<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopPartsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return 'Spare Part Terlaris';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $top = DashboardPeriod::metrics($this->pageFilters)->topParts();

        return [
            'datasets' => [[
                'label' => 'Terpakai (buah)',
                'data' => array_values($top),
                'backgroundColor' => '#22c55e',
            ]],
            'labels' => array_keys($top),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}