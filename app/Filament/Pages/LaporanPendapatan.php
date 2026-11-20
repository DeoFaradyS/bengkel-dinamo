<?php

namespace App\Filament\Pages;

use App\Enums\BookingType;
use App\Filament\Resources\Services\ServiceResource;
use App\Support\Dashboard\DashboardPeriod;
use App\Support\Dashboard\ServiceMetrics;
use Barryvdh\DomPDF\Facade\Pdf;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use UnitEnum;

/**
 * Laporan Pendapatan (aturan 23). Dasar hitung: uang diterima, yaitu servis Selesai yang sudah
 * lunas, menurut tanggal lunas (paid_at). Pendapatan = jasa + spare part + ongkos transport.
 * Servis Selesai yang belum lunas ditampilkan terpisah sebagai "Belum dibayar".
 * Tidak memuat pengeluaran, laba/rugi, atau modal spare part (pengembangan lanjutan).
 */
class LaporanPendapatan extends Page implements HasTable
{
    use HasFiltersForm, InteractsWithTable {
        InteractsWithTable::normalizeTableFilterValuesFromQueryString insteadof HasFiltersForm;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $navigationLabel = 'Pendapatan';
    protected static ?string $title = 'Laporan Pendapatan';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.laporan-pendapatan';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'md' => 3])
            ->components([
                DateRangePicker::make('range')->label('Periode')
                    ->format('d/m/Y')
                    ->ranges(DashboardPeriod::ranges())
                    ->useRangeLabels()
                    ->startDate(now()->startOfMonth())
                    ->endDate(now())
                    ->maxDate(now())
                    ->firstDayOfWeek(1)
                    ->autoApply(),
                ToggleButtons::make('tipe')->label('Tipe layanan')
                    ->options(['' => 'Semua'] + collect(BookingType::cases())
                        ->mapWithKeys(fn(BookingType $t) => [$t->value => $t->getLabel()])
                        ->all())
                    ->colors(['' => 'primary', 'workshop' => 'primary', 'home_service' => 'primary'])
                    ->default('')
                    ->inline()
                    ->columnSpan(['default' => 1, 'md' => 2]),
            ]);
    }

    public function metrics(): ServiceMetrics
    {
        return DashboardPeriod::metrics(['range' => $this->filters['range'] ?? null]);
    }

    public function bookingType(): ?string
    {
        return filled($this->filters['tipe'] ?? null) ? $this->filters['tipe'] : null;
    }

    public function periodLabel(): string
    {
        $m = $this->metrics();

        return $m->from()->isSameDay($m->to())
            ? $m->from()->translatedFormat('j F Y')
            : $m->from()->translatedFormat('j F Y') . ' - ' . $m->to()->translatedFormat('j F Y');
    }

    /** @return array{jobs: float, parts: float, transport: float, total: float, count: int} */
    public function summary(): array
    {
        return $this->metrics()->revenueBreakdown($this->bookingType());
    }

    /** @return array{jobs: float, parts: float, transport: float, total: float, count: int} */
    public function unpaid(): array
    {
        return ServiceMetrics::unpaidNow($this->bookingType());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => $this->metrics()->paidQuery()
                ->when($this->bookingType(), fn($q, $type) => $q->where('booking_type', $type))
                ->withSum('items', 'price')
                ->withSum('serviceParts as parts_sum', DB::raw('quantity * unit_price')))
            ->defaultSort('paid_at')
            ->columns([
                TextColumn::make('paid_at')->label('Tanggal lunas')
                    ->dateTime('j M Y, H.i')
                    ->sortable(),
                TextColumn::make('booking_code')->label('Kode'),
                TextColumn::make('customer_name')->label('Pelanggan'),
                TextColumn::make('booking_type')->label('Tipe')
                    ->badge()
                    ->icon(false),
                TextColumn::make('items_sum_price')->label('Jasa')
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->default(0)
                    ->alignEnd(),
                TextColumn::make('parts_sum')->label('Spare part')
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->default(0)
                    ->alignEnd(),
                TextColumn::make('transport_fee')->label('Transport')
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->default(0)
                    ->alignEnd(),
                TextColumn::make('total')->label('Total')
                    ->state(fn($record) => (float) ($record->items_sum_price ?? 0)
                        + (float) ($record->parts_sum ?? 0)
                        + (float) ($record->transport_fee ?? 0))
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->weight('semibold')
                    ->alignEnd(),
            ])
            ->recordUrl(fn($record) => ServiceResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Belum ada pendapatan di periode ini')
            ->emptyStateDescription('Pendapatan dihitung dari servis yang sudah ditandai lunas.')
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
                    'laporan-pendapatan-' . $this->metrics()->from()->format('Ymd') . '-' . $this->metrics()->to()->format('Ymd') . '.pdf',
                )),
        ];
    }

    public function pdf(): string
    {
        $rows = $this->metrics()->paidQuery()
            ->when($this->bookingType(), fn($q, $type) => $q->where('booking_type', $type))
            ->withSum('items', 'price')
            ->withSum('serviceParts as parts_sum', DB::raw('quantity * unit_price'))
            ->orderBy('paid_at')
            ->get();

        return Pdf::loadView('reports.pendapatan', [
            'period' => $this->periodLabel(),
            'type' => $this->bookingType() ? BookingType::from($this->bookingType())->getLabel() : 'Semua',
            'summary' => $this->summary(),
            'unpaid' => $this->unpaid(),
            'rows' => $rows,
        ])->setPaper('a4')->output();
    }
}
