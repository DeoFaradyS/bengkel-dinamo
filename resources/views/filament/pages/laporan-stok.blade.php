<x-filament-panels::page>
    {{-- Kartu ringkasan gaya dasbor (aturan 31). Filter periode ada di header. --}}
    @livewire(\App\Filament\Widgets\Laporan\LaporanStokKpi::class, ['pageFilters' => $this->filters], key('stok-kpi'))

    {{-- Mutasi persediaan: tabel Filament, baris membuka riwayat stok (kartu stok) part itu. --}}
    {{ $this->table }}
</x-filament-panels::page>
