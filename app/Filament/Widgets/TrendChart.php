<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\DashboardPeriod;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Tren Booking dan Servis Selesai';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $trend = DashboardPeriod::metrics($this->pageFilters)->perDay();

        return [
            'datasets' => [
                [
                    'label' => 'Booking masuk',
                    'data' => $trend['bookings'],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Servis selesai',
                    'data' => $trend['completed'],
                    'borderColor' => '#22c55e',
                    'backgroundColor' => '#22c55e',
                ],
            ],
            'labels' => array_map(fn ($d) => Carbon::parse($d)->format('d/m'), $trend['dates']),
        ];
    }

    // Sumbu angka bulat saja (tidak ada "1,5 booking").
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}