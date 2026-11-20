<?php

namespace App\Filament\Pages\Concerns;

use App\Support\Dashboard\DashboardPeriod;
use App\Support\Dashboard\ServiceMetrics;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

/**
 * Bagian yang sama di semua halaman Laporan (aturan 30 dan 31): filter periode bentuk dasbor,
 * header sendiri (filter sejajar tombol Unduh PDF), dan breadcrumb grup menu > nama menu.
 * Halaman yang memakai trait ini juga memakai HasFiltersForm.
 */
trait LaporanPeriode
{
    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'sm' => 1, 'md' => 1, 'lg' => 1, 'xl' => 1, '2xl' => 1])
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

    public function metrics(): ServiceMetrics
    {
        return DashboardPeriod::metrics(['range' => $this->filters['range'] ?? null]);
    }

    public function periodLabel(): string
    {
        $m = $this->metrics();

        return $m->from()->isSameDay($m->to())
            ? $m->from()->translatedFormat('j F Y')
            : $m->from()->translatedFormat('j F Y') . ' - ' . $m->to()->translatedFormat('j F Y');
    }

    // Breadcrumb grup menu > nama menu, seperti halaman lain (contoh Servis > Daftar).
    public function getBreadcrumbs(): array
    {
        return [static::$navigationGroup, static::$navigationLabel];
    }

    // Header sendiri supaya filter periode sejajar tombol Unduh PDF.
    public function getHeader(): ?View
    {
        return view('filament.pages.laporan-header', ['page' => $this]);
    }
}
