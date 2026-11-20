<x-filament-panels::page>
    @php
        $rupiah = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $s = $this->summary();
        $u = $this->unpaid();
        $avg = $s['count'] > 0 ? $s['total'] / $s['count'] : 0;
        $persen = fn ($n) => $s['total'] > 0 ? round($n / $s['total'] * 100) . '%' : '0%';
    @endphp

    <x-filament::section>
        {{ $this->getFiltersForm() }}
        <p class="mt-3 text-sm text-gray-500">
            Periode {{ $this->periodLabel() }}. Pendapatan dihitung dari servis yang sudah lunas, menurut tanggal lunas.
        </p>
    </x-filament::section>

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Total pendapatan', $rupiah($s['total']), null],
            ['Servis lunas', $s['count'], null],
            ['Rata-rata per servis', $rupiah($avg), null],
            ['Belum dibayar', $rupiah($u['total']), $u['count'] . ' servis selesai belum lunas (semua tanggal)'],
        ] as [$label, $nilai, $ket])
            <x-filament::section>
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-1 text-2xl font-semibold text-gray-950">{{ $nilai }}</p>
                @if ($ket)
                    <p class="mt-1 text-sm text-gray-500">{{ $ket }}</p>
                @endif
            </x-filament::section>
        @endforeach
    </div>

    {{-- Rincian sumber pendapatan --}}
    <x-filament::section heading="Rincian sumber pendapatan">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="py-2 font-medium">Sumber</th>
                    <th class="py-2 font-medium text-right">Jumlah</th>
                    <th class="py-2 font-medium text-right w-24">Persen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ([['Jasa', $s['jobs']], ['Spare part', $s['parts']], ['Transport (Panggil Montir)', $s['transport']]] as [$label, $nilai])
                    <tr>
                        <td class="py-2 text-gray-950">{{ $label }}</td>
                        <td class="py-2 text-right tabular-nums">{{ $rupiah($nilai) }}</td>
                        <td class="py-2 text-right tabular-nums text-gray-500">{{ $persen($nilai) }}</td>
                    </tr>
                @endforeach
                <tr class="font-semibold">
                    <td class="py-2 text-gray-950">Total</td>
                    <td class="py-2 text-right tabular-nums">{{ $rupiah($s['total']) }}</td>
                    <td class="py-2 text-right tabular-nums text-gray-500">{{ $s['total'] > 0 ? '100%' : '0%' }}</td>
                </tr>
            </tbody>
        </table>
    </x-filament::section>

    {{-- Daftar servis lunas --}}
    {{ $this->table }}
</x-filament-panels::page>
