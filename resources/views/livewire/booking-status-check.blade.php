<div class="w-full max-w-3xl flex flex-col items-center gap-8">
    <form wire:submit="check" class="w-full max-w-[480px] flex flex-col gap-4">
        <div class="flex flex-col gap-1">
            <input type="text" wire:model="customer_name" placeholder="Nama saat booking"
                class="w-full p-4 bg-neutral-100 rounded-[10px] text-neutral-800 placeholder:text-neutral-400 outline-none focus:ring-2 focus:ring-red-500">
            @error('customer_name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-col gap-1">
            <input type="tel" wire:model="phone" placeholder="Nomor HP, contoh 081234567890"
                class="w-full p-4 bg-neutral-100 rounded-[10px] text-neutral-800 placeholder:text-neutral-400 outline-none focus:ring-2 focus:ring-red-500">
            @error('phone') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="px-6 py-3.5 bg-gradient-to-b from-red-500 to-red-600 rounded-[10px] text-white font-semibold flex justify-center items-center gap-4 disabled:opacity-60">
            <span wire:loading.remove wire:target="check">Cek Status &rarr;</span>
            <span wire:loading wire:target="check">Mencari...</span>
        </button>
    </form>

    @if ($results !== null)
        @if (count($results) === 0)
            <div class="w-full flex flex-col items-center gap-2 p-8 text-center bg-neutral-50 border border-neutral-200 rounded-2xl">
                <p class="text-xl font-semibold text-neutral-800">Booking tidak ditemukan</p>
                <p class="text-lg text-neutral-500">Pastikan nama dan nomor HP sama seperti saat booking.</p>
            </div>
        @else
            @php
                // Alur normal 4 langkah. Status lain (batal, ditolak, kedaluwarsa) tidak pakai langkah.
                $langkah = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai'];

                // Warna ikut ServiceStatus::getColor(), sama dengan badge di panel admin.
                $warna = [
                    'gray' => ['bg-zinc-100 text-zinc-700 ring-zinc-600/10', 'bg-zinc-500'],
                    'info' => ['bg-blue-50 text-blue-700 ring-blue-600/10', 'bg-blue-500'],
                    'warning' => ['bg-amber-50 text-amber-700 ring-amber-600/10', 'bg-amber-500'],
                    'success' => ['bg-green-50 text-green-700 ring-green-600/10', 'bg-green-500'],
                    'danger' => ['bg-red-50 text-red-700 ring-red-600/10', 'bg-red-500'],
                ];

                $pesan = [
                    'pending' => 'Bengkel akan menghubungi untuk konfirmasi jadwal.',
                    'confirmed' => 'Jadwal sudah pasti. Sampai jumpa sesuai jadwal.',
                    'in_progress' => 'Sedang dikerjakan montir.',
                    'completed' => 'Servis selesai. Terima kasih sudah percaya Budi Dinamo.',
                    'cancelled' => 'Booking ini dibatalkan.',
                    'rejected' => 'Jadwal ini tidak bisa dilayani. Silakan booking jadwal lain.',
                    'expired' => 'Tidak dikonfirmasi sampai jadwal lewat. Silakan booking ulang.',
                ];
            @endphp

            <div class="w-full flex flex-col gap-5">
                @if (count($results) > 1)
                    <p class="text-lg text-neutral-500">{{ count($results) }} booking terakhir, terbaru di atas.</p>
                @endif

                @foreach ($results as $r)
                    @php
                        $status = \App\Enums\ServiceStatus::from($r['status']);
                        [$badge, $titik] = $warna[$status->getColor()] ?? $warna['gray'];
                        $posisi = array_search($r['status'], array_keys($langkah), true);
                    @endphp

                    <div wire:key="hasil-{{ $loop->index }}"
                        class="flex flex-col gap-6 p-6 sm:p-8 bg-white border border-neutral-200 rounded-2xl text-left">
                        <div class="flex flex-col gap-3">
                            <span class="self-start inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full ring-1 ring-inset text-lg font-semibold {{ $badge }}">
                                <span class="size-2.5 rounded-full {{ $titik }}"></span>
                                {{ $r['status_label'] }}
                            </span>
                            <p class="text-lg text-neutral-700 leading-relaxed">{{ $pesan[$r['status']] ?? '' }}</p>
                        </div>

                        @if ($posisi !== false)
                            <ol class="grid grid-cols-4 gap-2" aria-label="Tahap {{ $posisi + 1 }} dari 4">
                                @foreach ($langkah as $label)
                                    <li class="flex flex-col gap-2">
                                        <span class="h-2 rounded-full {{ $loop->index <= $posisi ? 'bg-red-500' : 'bg-neutral-200' }}"></span>
                                        <span class="text-sm sm:text-base {{ $loop->index === $posisi ? 'font-semibold text-neutral-900' : 'text-neutral-400' }}">{{ $label }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif

                        <dl class="flex flex-col gap-4 pt-6 border-t border-neutral-100 text-lg">
                            <div class="flex flex-col sm:flex-row sm:gap-6">
                                <dt class="sm:w-28 shrink-0 text-neutral-500">Jadwal</dt>
                                <dd class="font-semibold text-neutral-900">{{ $r['scheduled_at'] ?? '-' }}</dd>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:gap-6">
                                <dt class="sm:w-28 shrink-0 text-neutral-500">Jenis</dt>
                                <dd class="font-semibold text-neutral-900">{{ $r['type'] }}</dd>
                            </div>
                            @if ($r['complaint'])
                                <div class="flex flex-col sm:flex-row sm:gap-6">
                                    <dt class="sm:w-28 shrink-0 text-neutral-500">Keluhan</dt>
                                    <dd class="text-neutral-700 break-words">{{ $r['complaint'] }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
