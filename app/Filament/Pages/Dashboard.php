<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\KpiOverview;
use App\Filament\Widgets\StatusChart;
use App\Filament\Widgets\TopPartsChart;
use App\Filament\Widgets\TopServicesChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\TodaySchedule;
use Filament\Schemas\Components\Livewire;
use App\Filament\Widgets\TypeChart;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Filament\Schemas\Components\View;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function content(Schema $schema): Schema
    {
        // Aturan 24: bagian "Hari ini" (kondisi saat ini, tanpa filter) di atas, lalu bagian
        // "Kinerja periode" dengan filter tanggal tepat di atasnya.
        return $schema->components([
            View::make('filament.pages.dashboard-top')->columnSpanFull(),
            Livewire::make(TodaySchedule::class)->key('today-schedule')->columnSpanFull(),
            View::make('filament.pages.dashboard-period')->columnSpanFull(),
            $this->getWidgetsContentComponent(),
        ]);
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
                'xl' => 1,
                '2xl' => 1,
            ])
            ->components([
                DateRangePicker::make('range')
                    ->hiddenLabel()
                    ->extraAttributes(['style' => 'width:fit-content;max-width:100%;'], merge: true)
                    ->extraInputAttributes(['style' => 'field-sizing:content;min-width:8rem;'], merge: true)
                    ->prefixIcon('heroicon-m-calendar-days')
                    ->format('d/m/Y')
                    ->ranges(DashboardPeriod::ranges())
                    ->useRangeLabels()
                    ->startDate(now()->startOfMonth())
                    ->endDate(now())
                    ->maxDate(now())
                    ->firstDayOfWeek(1)
                    ->autoApply()
                    ->live(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            KpiOverview::class,
            RevenueChart::class,
            StatusChart::class,
            TypeChart::class,
            TopServicesChart::class,
            TopPartsChart::class,
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }
}