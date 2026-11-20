<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\LaporanPeriode;
use App\Support\Reports\StockMutation;
use Barryvdh\DomPDF\Facade\Pdf;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Laporan Stok (aturan 31): laporan mutasi persediaan per periode, pasangan Laporan Pendapatan.
 * Kolom standar: kode, nama barang, stok awal, masuk, keluar, penyesuaian, stok akhir.
 * Hitungan di App\Support\Reports\StockMutation. Riwayat per part (kartu stok) tetap di menu
 * Suku Cadang; baris tabel membuka halaman itu.
 */
class LaporanStok extends Page
{
    use HasFiltersForm, LaporanPeriode {
        LaporanPeriode::filtersForm insteadof HasFiltersForm;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;
    protected static ?string $navigationLabel = 'Stok';
    protected static ?string $title = 'Laporan Stok';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.laporan-stok';

    public function mutation(): StockMutation
    {
        return new StockMutation($this->metrics()->from(), $this->metrics()->to());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('unduh_pdf')
                ->label('Unduh PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn() => response()->streamDownload(
                    fn() => print ($this->pdf()),
                    'laporan-stok-' . $this->metrics()->from()->format('Ymd') . '-' . $this->metrics()->to()->format('Ymd') . '.pdf',
                )),
        ];
    }

    public function pdf(): string
    {
        $mutation = $this->mutation();

        return Pdf::loadView('reports.stok', [
            'period' => $this->periodLabel(),
            'rows' => $mutation->rows(),
            'totals' => $mutation->totals(),
        ])->setPaper('a4')->output();
    }
}
