<?php

namespace App\Filament\Widgets;

use App\Enums\ServiceStatus;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class StatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;
    protected ?string $maxHeight = '320px';

    public function getHeading(): ?string
    {
        return 'Komposisi Status Servis';
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
        $counts = DashboardPeriod::metrics($this->pageFilters)->statusBreakdown();

        $colors = [
            'pending' => '#9ca3af',
            'confirmed' => '#3b82f6',
            'in_progress' => '#f59e0b',
            'completed' => '#22c55e',
            'cancelled' => '#64748b',
            'rejected' => '#ef4444',
            'expired' => '#d6d3d1',
        ];

        return [
            'datasets' => [
                [
                    'data' => array_values($counts),
                    'backgroundColor' => array_map(fn($key) => $colors[$key] ?? '#d1d5db', array_keys($counts)),
                ]
            ],
            'labels' => array_map(fn($key) => ServiceStatus::from($key)->getLabel(), array_keys($counts)),
        ];
    }
}