<x-filament-panels::page>
    @php
        $rupiah = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $piutang = $this->unpaidRows();
        $w = ['pageFilters' => $this->filters];
    @endphp

    {{-- Filter periode, sama dengan dasbor (aturan 30). --}}
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;width:100%;">
        <p class="text-sm text-gray-500" style="flex:0 1 40rem;min-width:14rem;">
            {{ $this->periodLabel() }}. Dihitung dari servis yang sudah lunas, menurut tanggal lunas.
            Uang masuk belum dikurangi modal spare part.
        </p>
        <div style="flex:1;min-width:0;display:flex;justify-content:flex-end;">
            <div style="flex:none;">
                {!! $this->getFiltersForm()->toEmbeddedHtml() !!}
            </div>
        </div>
    </div>

    {{-- Kartu ringkasan dan grafik, gaya dasbor. --}}
    @livewire(\App\Filament\Widgets\Laporan\LaporanKpi::class, $w, key('lap-kpi'))

    <div class="grid gap-6 lg:grid-cols-3">
        @livewire(\App\Filament\Widgets\Laporan\RevenueSourceChart::class, $w, key('lap-sumber'))
        @livewire(\App\Filament\Widgets\Laporan\TopRevenueServicesChart::class, $w, key('lap-jasa'))
        @livewire(\App\Filament\Widgets\Laporan\TopRevenuePartsChart::class, $w, key('lap-part'))
    </div>

    {{-- Rincian transaksi --}}
    {{ $this->table }}

    {{-- 4. Piutang: selesai tapi belum dibayar, semua tanggal --}}
    <x-filament::section heading="Piutang (servis selesai, belum dibayar)"
        description="Semua tanggal, tidak ikut filter periode. Hilang dari daftar ini setelah ditandai lunas.">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-2 pr-4 font-medium">Tanggal servis</th>
                        <th class="py-2 px-2 font-medium">Kode</th>
                        <th class="py-2 px-2 font-medium">Pelanggan</th>
                        <th class="py-2 pl-2 font-medium text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($piutang as $p)
                        <tr>
                            <td class="py-2 pr-4 whitespace-nowrap">{{ $p->scheduled_at?->translatedFormat('j M Y') }}</td>
                            <td class="py-2 px-2">
                                <a href="{{ \App\Filament\Resources\Services\ServiceResource::getUrl('view', ['record' => $p]) }}"
                                    class="font-medium text-primary-600 hover:underline">{{ $p->booking_code }}</a>
                            </td>
                            <td class="py-2 px-2 text-gray-950">{{ $p->customer_name }}</td>
                            <td class="py-2 pl-2 text-right tabular-nums whitespace-nowrap">{{ $rupiah($p->grand_total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-500">Tidak ada piutang. Semua servis selesai sudah lunas.</td></tr>
                    @endforelse
                    @if ($piutang->isNotEmpty())
                        <tr class="font-semibold">
                            <td class="py-2 pr-4 text-gray-950" colspan="3">Jumlah ({{ $piutang->count() }} servis)</td>
                            <td class="py-2 pl-2 text-right tabular-nums whitespace-nowrap">{{ $rupiah($piutang->sum(fn ($p) => $p->grand_total)) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
