<?php

namespace App\Filament\Pages;

use App\Support\Dashboard\DashboardPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use App\Filament\Widgets\KpiOverview;
use App\Filament\Widgets\TrendChart;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(['default' => 1, 'md' => 3])
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
                        ->visible(fn(Get $get) => $get('preset') === 'custom'),
                    DatePicker::make('end_date')
                        ->label('Sampai')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(now())
                        ->visible(fn(Get $get) => $get('preset') === 'custom'),
                ]),
        ]);
    }

    // Widget dashboard menyusul; sementara hanya filter periode.
    public function getWidgets(): array
    {
        return [
            KpiOverview::class,
            TrendChart::class,
        ];
    }
}