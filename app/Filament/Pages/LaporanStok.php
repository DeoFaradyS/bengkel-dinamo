<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\LaporanPeriode;
use App\Filament\Resources\SpareParts\SparePartResource;
use App\Support\Reports\StockMutation;
use Barryvdh\DomPDF\Facade\Pdf;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Laporan Stok (aturan 31): laporan mutasi persediaan per periode, pasangan Laporan Pendapatan.
 * Kolom standar: kode, nama barang, stok awal, masuk, keluar, penyesuaian, stok akhir.
 * Hitungan di App\Support\Reports\StockMutation; tabel Filament dengan data array (cari, urut,
 * per halaman). Riwayat per part (kartu stok) tetap di menu Suku Cadang; baris membuka halaman itu.
 */
class LaporanStok extends Page implements HasTable
{
    use HasFiltersForm, InteractsWithTable, LaporanPeriode {
        InteractsWithTable::normalizeTableFilterValuesFromQueryString insteadof HasFiltersForm;
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

    public function table(Table $table): Table
    {
        $angka = fn(string $name, string $label) => TextColumn::make($name)->label($label)
            ->numeric()
            ->alignEnd()
            ->sortable();

        return $table
            ->heading('Mutasi persediaan')
            ->records(function (?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int $recordsPerPage): LengthAwarePaginator {
                $rows = collect($this->mutation()->rows())
                    ->when(filled($search), fn($c) => $c->filter(fn(array $r) => Str::contains(
                        Str::lower($r['code'] . ' ' . $r['name']),
                        Str::lower($search),
                    )))
                    ->when(filled($sortColumn), fn($c) => $c->sortBy($sortColumn, SORT_NATURAL, $sortDirection === 'desc'))
                    ->keyBy('id');

                return new LengthAwarePaginator(
                    $rows->forPage($page, $recordsPerPage),
                    $rows->count(),
                    $recordsPerPage,
                    $page,
                );
            })
            ->columns([
                TextColumn::make('code')->label('Kode')->color('gray')->sortable(),
                TextColumn::make('name')->label('Nama barang')
                    ->description(fn(array $record) => $record['archived'] ? 'Diarsip' : null)
                    ->sortable()
                    ->wrap(),
                $angka('opening', 'Stok awal'),
                $angka('in', 'Masuk'),
                $angka('out', 'Keluar'),
                $angka('adjustment', 'Penyesuaian')
                    ->formatStateUsing(fn(int $state) => $state > 0 ? '+' . $state : (string) $state)
                    ->color(fn(int $state) => $state < 0 ? 'danger' : null),
                $angka('closing', 'Stok akhir')->weight('semibold'),
            ])
            ->searchable()
            ->defaultSort('code')
            ->recordUrl(fn(array $record) => $record['archived'] ? null : SparePartResource::getUrl('edit', ['record' => $record['id']]))
            ->emptyStateHeading('Belum ada suku cadang')
            ->paginated([10, 25, 50]);
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
