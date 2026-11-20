<x-filament-panels::page>
    @php
        $rows = $this->mutation()->rows();
        $tanda = fn (int $n) => $n > 0 ? '+' . $n : (string) $n;
    @endphp

    {{-- Kartu ringkasan gaya dasbor (aturan 31). Filter periode ada di header. --}}
    @livewire(\App\Filament\Widgets\Laporan\LaporanStokKpi::class, ['pageFilters' => $this->filters], key('stok-kpi'))

    {{-- Mutasi persediaan: kolom standar. Baris membuka riwayat stok (kartu stok) part itu. --}}
    <x-filament::section heading="Mutasi persediaan">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-2 pr-4 font-medium">Kode</th>
                        <th class="py-2 px-2 font-medium">Nama barang</th>
                        <th class="py-2 px-2 font-medium text-right">Stok awal</th>
                        <th class="py-2 px-2 font-medium text-right">Masuk</th>
                        <th class="py-2 px-2 font-medium text-right">Keluar</th>
                        <th class="py-2 px-2 font-medium text-right">Penyesuaian</th>
                        <th class="py-2 px-2 font-medium text-right">Stok akhir</th>
                        <th class="py-2 pl-2 font-medium">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rows as $r)
                        @php $url = \App\Filament\Resources\SpareParts\SparePartResource::getUrl('edit', ['record' => $r['id']]); @endphp
                        <tr class="{{ $r['archived'] ? '' : 'cursor-pointer hover:bg-gray-50' }}"
                            @unless ($r['archived']) onclick="window.location='{{ $url }}'" @endunless>
                            <td class="py-2 pr-4 whitespace-nowrap text-gray-500">{{ $r['code'] }}</td>
                            <td class="py-2 px-2 text-gray-950">
                                {{ $r['name'] }}
                                @if ($r['archived']) <span class="text-xs text-gray-400">(diarsip)</span> @endif
                            </td>
                            <td class="py-2 px-2 text-right tabular-nums">{{ $r['opening'] }}</td>
                            <td class="py-2 px-2 text-right tabular-nums">{{ $r['in'] }}</td>
                            <td class="py-2 px-2 text-right tabular-nums">{{ $r['out'] }}</td>
                            <td class="py-2 px-2 text-right tabular-nums">{{ $tanda($r['adjustment']) }}</td>
                            <td class="py-2 px-2 text-right tabular-nums font-semibold">{{ $r['closing'] }}</td>
                            <td class="py-2 pl-2 whitespace-nowrap">
                                @if ($r['low'])
                                    <x-filament::badge color="danger">Hampir habis</x-filament::badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-6 text-center text-gray-500">Belum ada suku cadang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
