<x-filament-panels::page>
    @php
        $w = ['pageFilters' => $this->filters];
    @endphp

    {{-- Filter periode, sama dengan dasbor (aturan 30). --}}
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;width:100%;">
        <p class="text-sm text-gray-500" style="flex:0 1 40rem;min-width:14rem;">
            {{ $this->periodLabel() }}. Dihitung dari servis yang sudah lunas, menurut tanggal lunas.
            Pendapatan belum dikurangi modal spare part.
        </p>
        <div style="flex:1;min-width:0;display:flex;justify-content:flex-end;">
            <div style="flex:none;">
                {!! $this->getFiltersForm()->toEmbeddedHtml() !!}
            </div>
        </div>
    </div>

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
