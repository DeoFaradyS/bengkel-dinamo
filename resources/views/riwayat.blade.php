@extends('layouts.customer')

@section('title', 'Riwayat Booking - Budi Dinamo')

@section('content')
    @php
        $field = 'rounded-[10px] border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-800 outline-none transition-colors focus:border-red-600 focus:ring-1 focus:ring-red-600';
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $sortUrl = fn (string $col) => request()->fullUrlWithQuery([
            'sort' => $col,
            'arah' => ($sort === $col && $arah === 'desc') ? 'asc' : 'desc',
            'page' => null,
        ]);
        $chevron = fn (string $col) => $sort === $col && $arah === 'asc' ? 'm18 15-6-6-6 6' : 'm6 9 6 6 6-6';
    @endphp

    <h1 class="text-3xl font-bold text-neutral-800">Riwayat Booking</h1>
    <p class="mt-2 mb-6 text-neutral-500">Semua booking servis dari akun ini.</p>

    <form method="GET" action="{{ route('riwayat') }}" class="overflow-hidden rounded-[10px] border border-neutral-200 bg-white">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="arah" value="{{ $arah }}">

        @if ($services->total() > 0 || $filtered)
            <div class="flex flex-col gap-3 border-b border-neutral-200 p-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <select name="tipe" aria-label="Filter tipe" onchange="this.form.submit()" class="{{ $field }}">
                    <option value="">Semua tipe</option>
                    @foreach ($tipeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($tipe === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="status" aria-label="Filter status" onchange="this.form.submit()" class="{{ $field }}">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="bayar" aria-label="Filter pembayaran" onchange="this.form.submit()" class="{{ $field }}">
                    <option value="">Semua pembayaran</option>
                    <option value="lunas" @selected($bayar === 'lunas')>Lunas</option>
                    <option value="belum_lunas" @selected($bayar === 'belum_lunas')>Belum Lunas</option>
                </select>

                @if ($filtered)
                    <a href="{{ route('riwayat') }}" class="px-2 text-sm font-semibold text-neutral-600 transition-colors hover:text-red-600">Reset</a>
                @endif
            </div>
        @endif

        @if ($services->isEmpty())
            <div class="flex flex-col items-center gap-4 px-6 py-14 text-center">
                @if ($filtered)
                    <p class="text-neutral-600">Tidak ada booking yang cocok.</p>
                    <a href="{{ route('riwayat') }}" class="rounded-[10px] border border-neutral-200 px-6 py-3 font-semibold text-neutral-800 transition-colors hover:bg-neutral-50">
                        Reset filter
                    </a>
                @else
                    <p class="text-neutral-600">Belum ada booking.</p>
                    <a href="/#booking" class="rounded-[10px] bg-neutral-800 px-6 py-3 font-semibold text-white transition-colors hover:bg-neutral-700">
                        Booking Sekarang
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold text-neutral-700">
                        <tr>
                            <th scope="col" class="w-14 px-4 py-3">No</th>
                            <th scope="col" class="px-4 py-3">Kode</th>
                            <th scope="col" class="px-4 py-3" @if ($sort === 'tanggal') aria-sort="{{ $arah === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                <a href="{{ $sortUrl('tanggal') }}" class="inline-flex items-center gap-1 transition-colors hover:text-red-600">
                                    Tanggal &amp; Jam
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 {{ $sort === 'tanggal' ? '' : 'opacity-30' }}" aria-hidden="true"><path d="{{ $chevron('tanggal') }}" /></svg>
                                </a>
                            </th>
                            <th scope="col" class="px-4 py-3">Tipe</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3">Pembayaran</th>
                            <th scope="col" class="px-4 py-3 text-right" @if ($sort === 'total') aria-sort="{{ $arah === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                <a href="{{ $sortUrl('total') }}" class="inline-flex items-center gap-1 transition-colors hover:text-red-600">
                                    Total
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 {{ $sort === 'total' ? '' : 'opacity-30' }}" aria-hidden="true"><path d="{{ $chevron('total') }}" /></svg>
                                </a>
                            </th>
                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        @foreach ($services as $service)
                            @php
                                $done = $service->status === \App\Enums\ServiceStatus::Completed;
                                $total = (float) ($service->items_sum_price ?? 0)
                                    + (float) ($service->parts_sum ?? 0)
                                    + (float) ($service->transport_fee ?? 0);
                            @endphp
                            <tr class="transition-colors hover:bg-neutral-50">
                                <td class="px-4 py-3 text-neutral-500">{{ $services->firstItem() + $loop->index }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-semibold tabular-nums text-neutral-800">{{ $service->booking_code ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-neutral-800">
                                    {{ $service->scheduled_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <x-status-badge :status="$service->booking_type" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <x-status-badge :status="$service->status" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($done)
                                        <x-status-badge :label="$service->paid_at ? 'Lunas' : 'Belum Lunas'" :color="$service->paid_at ? 'success' : 'warning'" />
                                    @else
                                        <span class="text-neutral-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-neutral-800">
                                    {{ $total > 0 ? $rupiah($total) : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-4">
                                        <a href="{{ route('riwayat.show', $service->id) }}"
                                            class="inline-flex items-center gap-1 text-sm font-semibold text-red-600 transition-colors hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                                            Lihat
                                        </a>
                                        @if ($done)
                                            <a href="{{ route('riwayat.invoice', $service->id) }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 text-sm font-semibold text-neutral-600 transition-colors hover:text-red-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="M6 9V2h12v7" /><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><rect width="12" height="8" x="6" y="14" /></svg>
                                                Invoice
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-neutral-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <label class="flex items-center gap-2 text-sm text-neutral-500">
                    Per halaman
                    <select name="per_page" onchange="this.form.submit()" class="{{ $field }} py-1.5">
                        @foreach ([10, 25, 50] as $n)
                            <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </label>

                <p class="text-sm text-neutral-500">
                    Menampilkan {{ $services->firstItem() }} sampai {{ $services->lastItem() }} dari {{ $services->total() }} booking
                </p>

                {{ $services->links('pagination.filament') }}
            </div>
        @endif
    </form>
@endsection