@extends('layouts.customer')

@section('title', 'Detail Booking - Budi Dinamo')

@section('content')
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $done = $service->status === \App\Enums\ServiceStatus::Completed;
        $isHome = $service->booking_type === \App\Enums\BookingType::HomeService;
        $showMap = $isHome && filled($service->latitude) && filled($service->longitude);
        $dt = 'text-sm text-neutral-500';
        $dd = 'mt-1 text-sm font-semibold text-neutral-800';
        $th = 'px-4 py-3';
    @endphp

    <a href="{{ route('riwayat') }}"
        class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-neutral-600 transition-colors hover:text-red-600">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="M19 12H5" /><path d="m12 19-7-7 7-7" /></svg>
        Riwayat Booking
    </a>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-3xl font-bold text-neutral-800">Detail Booking</h1>

        @if ($service->status === \App\Enums\ServiceStatus::Pending)
            <button type="button" x-data x-on:click="$dispatch('buka-batal')"
                class="inline-flex items-center gap-2 rounded-[10px] border border-neutral-300 bg-white px-5 py-3 text-sm font-semibold text-neutral-700 transition-colors hover:border-red-400 hover:text-red-600">
                Batalkan booking
            </button>
        @endif

        @if ($done)
            <a href="{{ route('riwayat.invoice', $service->id) }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 rounded-[10px] bg-neutral-800 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-neutral-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="M6 9V2h12v7" /><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><rect width="12" height="8" x="6" y="14" /></svg>
                Cetak Invoice
            </a>
        @endif
    </div>

    @if (session('status'))
        <p class="mb-6 rounded-[10px] border border-neutral-200 bg-neutral-50 px-4 py-3 text-sm font-semibold text-neutral-800" role="status">
            {{ session('status') }}
            @if ($service->status === \App\Enums\ServiceStatus::Cancelled)
                <a href="/#booking" class="ml-1 text-red-600 underline underline-offset-2">Buat booking baru</a>
            @endif
        </p>
    @endif

    @if ($service->status === \App\Enums\ServiceStatus::Pending)
        {{-- Konfirmasi batal: pelanggan boleh membatalkan sendiri hanya selama Menunggu. --}}
        <form method="POST" action="{{ route('riwayat.batal', $service->id) }}" x-data="{ buka: false }"
            x-on:buka-batal.window="buka = true" x-show="buka" style="display:none"
            class="mb-6 flex flex-col gap-4 rounded-[10px] border border-red-200 bg-red-50 p-5">
            @csrf
            <p class="text-base font-semibold text-neutral-900">Batalkan booking ini?</p>
            <p class="text-sm text-neutral-700">Booking yang dibatalkan tidak bisa dikembalikan, tapi kamu bisa langsung booking lagi.</p>

            <fieldset class="flex flex-col gap-2">
                <legend class="mb-2 text-sm text-neutral-600">Alasan (boleh dikosongkan)</legend>
                @foreach (\App\Actions\CancelBookingByCustomer::REASONS as $opsi)
                    <label class="flex cursor-pointer items-center gap-3 text-sm text-neutral-800">
                        <input type="radio" name="alasan" value="{{ $opsi }}" class="size-4 accent-red-600">
                        {{ $opsi }}
                    </label>
                @endforeach
            </fieldset>

            <div class="flex flex-wrap gap-3">
                <button type="submit"
                    class="rounded-[10px] bg-red-600 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-red-700">
                    Ya, batalkan
                </button>
                <button type="button" x-on:click="buka = false"
                    class="rounded-[10px] border border-neutral-300 bg-white px-5 py-3 text-sm font-semibold text-neutral-700 hover:border-neutral-400">
                    Kembali
                </button>
            </div>
        </form>
    @endif

    <div class="flex flex-col gap-6">

        <section class="rounded-[10px] border border-neutral-200 bg-white">
            <header class="border-b border-neutral-200 px-6 py-4">
                <h2 class="text-base font-semibold text-neutral-800">Data Servis</h2>
            </header>

            <dl class="grid gap-x-8 gap-y-5 p-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="{{ $dt }}">Kode Booking</dt>
                    <dd class="{{ $dd }} tabular-nums">{{ $service->booking_code ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="{{ $dt }}">Nama Pelanggan</dt>
                    <dd class="{{ $dd }}">{{ $service->customer_name ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="{{ $dt }}">No. HP</dt>
                    <dd class="{{ $dd }}">{{ $service->customer_phone ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="{{ $dt }}">Tanggal &amp; Jam</dt>
                    <dd class="{{ $dd }}">{{ $service->scheduled_at?->translatedFormat('j M Y, H.i') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="{{ $dt }}">Tipe Layanan</dt>
                    <dd class="mt-1"><x-status-badge :status="$service->booking_type" /></dd>
                </div>
                <div>
                    <dt class="{{ $dt }}">Status</dt>
                    <dd class="mt-1"><x-status-badge :status="$service->status" /></dd>
                </div>

                @if ($done)
                    <div>
                        <dt class="{{ $dt }}">Pembayaran</dt>
                        <dd class="mt-1">
                            <x-status-badge :label="$service->paid_at ? 'Lunas' : 'Belum Lunas'" :color="$service->paid_at ? 'success' : 'warning'" />
                        </dd>
                    </div>
                @endif

                @if (filled($service->paid_at))
                    <div>
                        <dt class="{{ $dt }}">Dibayar pada</dt>
                        <dd class="{{ $dd }}">{{ $service->paid_at->translatedFormat('j M Y, H.i') }}</dd>
                    </div>
                @endif

                <div class="sm:col-span-2">
                    <dt class="{{ $dt }}">Keluhan</dt>
                    <dd class="{{ $dd }} whitespace-pre-line">{{ $service->complaint ?: '-' }}</dd>
                </div>

                {{-- D3: alasan hanya untuk booking yang dibatalkan atau ditolak, sama dengan Cek Booking. --}}
                @if (in_array($service->status, [\App\Enums\ServiceStatus::Cancelled, \App\Enums\ServiceStatus::Rejected], true) && filled($service->cancel_reason))
                    <div class="sm:col-span-2">
                        <dt class="{{ $dt }}">Alasan</dt>
                        <dd class="{{ $dd }} whitespace-pre-line">{{ $service->cancel_reason }}</dd>
                    </div>
                @endif

                @if (filled($service->address_note))
                    <div class="sm:col-span-2">
                        <dt class="{{ $dt }}">Detail alamat</dt>
                        <dd class="{{ $dd }} whitespace-pre-line">{{ $service->address_note }}</dd>
                    </div>
                @endif

                @if ($showMap)
                    <div class="sm:col-span-2">
                        <dt class="{{ $dt }}">Peta</dt>
                        <dd class="mt-1">
                            <a href="https://www.google.com/maps?q={{ $service->latitude }},{{ $service->longitude }}" target="_blank" rel="noopener"
                                class="text-sm font-semibold text-sky-700 transition-colors hover:text-sky-800">Buka di Google Maps</a>
                        </dd>
                    </div>
                @endif
            </dl>
        </section>

        @if ($service->items->isNotEmpty())
            <section class="rounded-[10px] border border-neutral-200 bg-white">
                <header class="border-b border-neutral-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-neutral-800">Jasa</h2>
                </header>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[420px] text-left text-sm">
                        <thead class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold text-neutral-700">
                            <tr>
                                <th scope="col" class="{{ $th }}">Jasa</th>
                                <th scope="col" class="{{ $th }} w-48 text-right">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            @foreach ($service->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-neutral-800">{{ $item->name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-neutral-800">{{ $rupiah($item->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($service->serviceParts->isNotEmpty())
            <section class="rounded-[10px] border border-neutral-200 bg-white">
                <header class="border-b border-neutral-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-neutral-800">Spare Part</h2>
                </header>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold text-neutral-700">
                            <tr>
                                <th scope="col" class="{{ $th }}">Part</th>
                                <th scope="col" class="{{ $th }} w-24 text-right">Qty</th>
                                <th scope="col" class="{{ $th }} w-44 text-right">Harga satuan</th>
                                <th scope="col" class="{{ $th }} w-44 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            @foreach ($service->serviceParts as $part)
                                <tr>
                                    <td class="px-4 py-3 text-neutral-800">{{ $part->sparePart?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right text-neutral-800">{{ number_format((float) $part->quantity, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-neutral-800">{{ $rupiah($part->unit_price) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-neutral-800">{{ $rupiah($part->subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="rounded-[10px] border border-neutral-200 bg-white">
            <header class="border-b border-neutral-200 px-6 py-4">
                <h2 class="text-base font-semibold text-neutral-800">Ringkasan biaya</h2>
            </header>

            <div class="flex flex-col gap-3 p-6 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <span class="text-neutral-500">Subtotal jasa</span>
                    <span class="font-semibold text-neutral-800">{{ $rupiah($service->labor_total) }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-neutral-500">Subtotal spare part</span>
                    <span class="font-semibold text-neutral-800">{{ $rupiah($service->parts_total) }}</span>
                </div>
                @if ((float) $service->transport_fee > 0)
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-neutral-500">Transport (BBM)</span>
                        <span class="font-semibold text-neutral-800">{{ $rupiah($service->transport_fee) }}</span>
                    </div>
                @endif

                <div class="mt-2 flex items-center justify-between gap-4 rounded-[10px] bg-neutral-50 px-4 py-3.5">
                    <span class="font-bold text-neutral-800">Total Servis</span>
                    <span class="text-lg font-bold text-neutral-800">{{ $rupiah($service->grand_total) }}</span>
                </div>
            </div>
        </section>
    </div>
@endsection