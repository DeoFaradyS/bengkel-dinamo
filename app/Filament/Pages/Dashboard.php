<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\KpiOverview;
use App\Filament\Widgets\StatusChart;
use App\Filament\Widgets\TopPartsChart;
use App\Filament\Widgets\TopServicesChart;
use App\Filament\Widgets\TrendChart;
use App\Filament\Widgets\TypeChart;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Schemas\Components\Utilities\Get;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label(fn () => DashboardPeriod::label($this->filters))
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->modalHeading('Pilih periode')
                ->modalWidth('md')
                ->modalSubmitActionLabel('Terapkan')
                ->schema([
                    Select::make('preset')
                        ->label('Periode')
                        ->options(DashboardPeriod::options())
                        ->default(DashboardPeriod::DEFAULT)
                        ->native(false)
                        ->selectablePlaceholder(false)
                        ->live(),
                    DatePicker::make('start_date')
                        ->label('Dari')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(now())
                        ->visible(fn (Get $get) => $get('preset') === 'custom'),
                    DatePicker::make('end_date')
                        ->label('Sampai')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(now())
                        ->visible(fn (Get $get) => $get('preset') === 'custom'),
                ]),
        ];
    }

    public function getWidgets(): array
    {
        return [
            KpiOverview::class,
            TrendChart::class,
            StatusChart::class,
            TypeChart::class,
            TopServicesChart::class,
            TopPartsChart::class,
        ];
    }
}