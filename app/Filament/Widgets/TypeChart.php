<?php

namespace App\Filament\Widgets;

use App\Enums\BookingType;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TypeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;
        protected ?string $maxHeight = '320px';

    public function getHeading(): ?string
    {
        return 'Home Service vs Ke Bengkel';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

        protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 16,
                    ],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $counts = DashboardPeriod::metrics($this->pageFilters)->typeBreakdown();

        $colors = [
            'workshop' => '#3b82f6',
            'home_service' => '#f59e0b',
        ];

        return [
            'datasets' => [[
                'data' => array_values($counts),
                'backgroundColor' => array_map(fn ($key) => $colors[$key], array_keys($counts)),
            ]],
            'labels' => array_map(fn ($key) => BookingType::from($key)->getLabel(), array_keys($counts)),
        ];
    }
}