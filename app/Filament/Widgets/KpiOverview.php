<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\DashboardPeriod;
use App\Support\Dashboard\KpiSummary;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KpiOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    public function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $metrics = DashboardPeriod::metrics($this->pageFilters);

        return collect(KpiSummary::make($metrics))
            ->map(fn(array $kpi) => Stat::make($kpi['label'], $kpi['value'])
                ->extraAttributes(['class' => 'kpi-stat kpi-' . $kpi['key']]))
            ->all();
    }
}