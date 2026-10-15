<div>
    @if ($submitted)
        <div class="flex flex-col gap-2 py-6 text-center">
            <p class="text-lg font-semibold text-neutral-800">Booking terkirim</p>
            <p class="text-neutral-500">Kami akan menghubungi untuk konfirmasi.</p>
        </div>
    @else
        <form wire:submit="submit" class="flex flex-col gap-4">
            <div style="display:none" aria-hidden="true">
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="flex flex-col gap-1">
                <input type="text" wire:model="customer_name" placeholder="Nama Lengkap"
                    class="w-full p-4 bg-neutral-100 rounded-[10px] text-neutral-800 placeholder:text-neutral-400 outline-none focus:ring-2 focus:ring-red-500">
                @error('customer_name') <small class="text-sm text-red-600">{{ $message }}</small> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <input type="tel" wire:model="phone" placeholder="Nomor HP, contoh 081234567890"
                    class="w-full p-4 bg-neutral-100 rounded-[10px] text-neutral-800 placeholder:text-neutral-400 outline-none focus:ring-2 focus:ring-red-500">
                @error('phone') <small class="text-sm text-red-600">{{ $message }}</small> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <input type="datetime-local" wire:model="scheduled_at"
                    class="w-full p-4 bg-neutral-100 rounded-[10px] text-neutral-800 outline-none focus:ring-2 focus:ring-red-500">
                @error('scheduled_at') <small class="text-sm text-red-600">{{ $message }}</small> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <textarea wire:model="complaint" placeholder="Keluhan" rows="3"
                    class="w-full p-4 bg-neutral-100 rounded-[10px] text-neutral-800 placeholder:text-neutral-400 outline-none focus:ring-2 focus:ring-red-500"></textarea>
                @error('complaint') <small class="text-sm text-red-600">{{ $message }}</small> @enderror
            </div>

            @error('throttle') <small class="text-sm text-red-600">{{ $message }}</small> @enderror

            <button type="submit" wire:loading.attr="disabled"
                class="px-6 py-3.5 bg-gradient-to-b from-red-500 to-red-600 rounded-[10px] text-white font-semibold flex justify-center items-center gap-4 disabled:opacity-60">
                <span wire:loading.remove>Booking Sekarang →</span>
                <span wire:loading>Mengirim...</span>
            </button>
        </form>
    @endif
</div>