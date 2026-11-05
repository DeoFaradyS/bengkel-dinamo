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
            <p class="p-4 bg-neutral-50 border border-neutral-200 rounded-[10px] text-sm text-neutral-600">
                Booking tidak ditemukan. Cek nama dan nomor HP sama seperti saat booking.
            </p>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($results as $r)
                    <div class="flex flex-col gap-1 p-4 bg-neutral-50 border border-neutral-200 rounded-[10px]">
                        <p class="font-semibold text-neutral-800">{{ $r['status_label'] }}</p>
                        <p class="text-sm text-neutral-600">{{ $r['type'] }} &middot; {{ $r['scheduled_at'] ?? '-' }}</p>
                        @if ($r['complaint'])
                            <p class="text-sm text-neutral-500">{{ $r['complaint'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>