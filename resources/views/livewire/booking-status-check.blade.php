<div class="w-full max-w-[480px] flex flex-col gap-4">
    <form wire:submit="check" class="flex flex-col gap-4">
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
            <div class="flex flex-col items-center gap-1 p-6 text-center bg-neutral-50 border border-neutral-200 rounded-2xl">
                <p class="font-semibold text-neutral-800">Booking tidak ditemukan</p>
                <p class="text-sm text-neutral-500">Pastikan nama dan nomor HP sama seperti saat booking.</p>
            </div>
        @else
            @php
                // Alur normal 4 langkah. Status lain (batal, ditolak, kedaluwarsa) tidak pakai langkah.
                $langkah = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai'];
                $info = [
                    'pending' => ['bg-amber-50 text-amber-700', 'bg-amber-500', 'Bengkel akan menghubungi untuk konfirmasi jadwal.'],
                    'confirmed' => ['bg-blue-50 text-blue-700', 'bg-blue-500', 'Jadwal sudah pasti. Sampai jumpa sesuai jadwal.'],
                    'in_progress' => ['bg-orange-50 text-orange-700', 'bg-orange-500', 'Sedang dikerjakan montir.'],
                    'completed' => ['bg-green-50 text-green-700', 'bg-green-500', 'Servis selesai. Terima kasih sudah percaya Budi Dinamo.'],
                    'cancelled' => ['bg-neutral-100 text-neutral-600', 'bg-neutral-400', 'Booking ini dibatalkan.'],
                    'rejected' => ['bg-red-50 text-red-700', 'bg-red-500', 'Jadwal ini tidak bisa dilayani. Silakan booking jadwal lain.'],
                    'expired' => ['bg-neutral-100 text-neutral-600', 'bg-neutral-400', 'Tidak dikonfirmasi sampai jadwal lewat. Silakan booking ulang.'],
                ];
            @endphp

            <div class="flex flex-col gap-3">
                @if (count($results) > 1)
                    <p class="text-sm text-neutral-500">{{ count($results) }} booking terakhir, terbaru di atas.</p>
                @endif

                @foreach ($results as $r)
                    @php
                        [$pill, $dot, $pesan] = $info[$r['status']] ?? $info['cancelled'];
                        $posisi = array_search($r['status'], array_keys($langkah), true);
                    @endphp

                    <div wire:key="hasil-{{ $loop->index }}" class="flex flex-col gap-4 p-5 bg-white border border-neutral-200 rounded-2xl text-left">
                        <div class="flex flex-col gap-2">
                            <span class="self-start inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold {{ $pill }}">
                                <span class="size-2 rounded-full {{ $dot }}"></span>
                                {{ $r['status_label'] }}
                            </span>
                            <p class="text-sm text-neutral-600">{{ $pesan }}</p>
                        </div>

                        @if ($posisi !== false)
                            <ol class="grid grid-cols-4 gap-1.5" aria-label="Tahap {{ $posisi + 1 }} dari 4">
                                @foreach ($langkah as $label)
                                    <li class="flex flex-col gap-1.5">
                                        <span class="h-1.5 rounded-full {{ $loop->index <= $posisi ? 'bg-red-500' : 'bg-neutral-200' }}"></span>
                                        <span class="text-xs {{ $loop->index === $posisi ? 'font-semibold text-neutral-800' : 'text-neutral-400' }}">{{ $label }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif

                        <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 pt-4 border-t border-neutral-100 text-sm">
                            <dt class="text-neutral-400">Jadwal</dt>
                            <dd class="font-medium text-neutral-800">{{ $r['scheduled_at'] ?? '-' }}</dd>
                            <dt class="text-neutral-400">Jenis</dt>
                            <dd class="font-medium text-neutral-800">{{ $r['type'] }}</dd>
                            @if ($r['complaint'])
                                <dt class="text-neutral-400">Keluhan</dt>
                                <dd class="text-neutral-600 break-words">{{ $r['complaint'] }}</dd>
                            @endif
                        </dl>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
