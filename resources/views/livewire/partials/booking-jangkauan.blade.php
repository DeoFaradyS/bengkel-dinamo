{{--
    Hasil cek jarak Panggil Montir (aturan 40). Hijau = dalam jangkauan, merah = di luar jangkauan
    beserta jalan keluarnya. $ringkas = true di modal peta: judul dan deskripsi saja, tanpa tombol.
--}}
@php($ringkas = $ringkas ?? false)

@if ($distanceText)
    <div role="status" wire:loading.remove wire:target="hitungJarak"
        class="flex items-start gap-3 rounded-[10px] border border-green-200 bg-green-50 p-3 text-left text-sm">
        <svg class="mt-0.5 size-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
        </svg>
        <p class="text-green-800"><span class="font-semibold">Dalam jangkauan</span> · {{ \Illuminate\Support\Str::after($distanceText, 'Jarak ke bengkel ') }} dari bengkel</p>
    </div>
@endif

@if ($distanceError)
    <div role="alert" wire:loading.remove wire:target="hitungJarak"
        class="flex items-start gap-3 rounded-[10px] border border-red-200 bg-red-50 p-3 text-left text-sm">
        <svg class="mt-0.5 size-5 shrink-0 text-red-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
        </svg>
        <div class="flex flex-col gap-1">
            <p class="font-semibold text-red-800">Lokasi di luar jangkauan montir</p>
            <p class="text-red-700">Lokasimu sekitar {{ $distanceKm }} km dari bengkel. Montir hanya melayani sampai {{ $maxKm }} km.</p>
            @unless ($ringkas)
                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2">
                    <button type="button" wire:click="pilihKeBengkel"
                        class="min-h-11 rounded-lg bg-white px-4 py-2 font-semibold text-red-700 ring-1 ring-red-200 transition-colors hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Pilih Ke Bengkel saja
                    </button>
                    <a href="{{ \App\Support\WhatsAppLink::make(\App\Support\WorkshopContact::phone(), 'Halo, lokasi saya sekitar ' . $distanceKm . ' km dari bengkel. Apakah montir masih bisa datang?') }}"
                        target="_blank" rel="noopener"
                        class="font-semibold text-red-700 underline underline-offset-2 hover:text-red-800">
                        Tanya lewat WhatsApp
                    </a>
                </div>
            @endunless
        </div>
    </div>
@endif
