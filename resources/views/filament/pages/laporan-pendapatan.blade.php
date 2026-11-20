<x-filament-panels::page>
    @php
        $w = ['pageFilters' => $this->filters];
    @endphp

    {{-- Filter periode ada di header, sejajar tombol Unduh PDF (laporan-pendapatan-header). --}}
    {{-- Kartu ringkasan dan donat sumber pendapatan, gaya dasbor. --}}
    @livewire(\App\Filament\Widgets\Laporan\LaporanKpi::class, $w, key('lap-kpi'))

    {{-- Tabel servis lunas (kiri, lebar) sejajar donat sumber pendapatan (kanan). Di layar sempit atas-bawah. --}}
    <div class="grid gap-6 xl:grid-cols-3" style="align-items:start;">
        <div class="min-w-0 xl:col-span-2">
            {{ $this->table }}
        </div>
        @livewire(\App\Filament\Widgets\Laporan\RevenueSourceChart::class, $w, key('lap-sumber'))
    </div>
</x-filament-panels::page>
