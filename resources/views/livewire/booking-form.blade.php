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
                <div class="grid grid-cols-2 gap-1 p-1 bg-neutral-100 rounded-[10px]">
                    @foreach (\App\Enums\BookingType::cases() as $type)
                        <label wire:key="type-{{ $type->value }}"
                            class="py-3 text-center rounded-lg cursor-pointer font-semibold transition focus-within:ring-2 focus-within:ring-red-500 {{ $booking_type === $type->value ? 'bg-white text-red-600 shadow-sm' : 'text-neutral-500' }}">
                            <input type="radio" wire:model.live="booking_type" value="{{ $type->value }}"
                                class="sr-only">
                            {{ $type->getLabel() }}
                        </label>
                    @endforeach
                </div>
                @error('booking_type') <small class="text-sm text-red-600">{{ $message }}</small> @enderror
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

            @if ($booking_type === \App\Enums\BookingType::HomeService->value)
                <div x-data="{ locating: false, locateError: '' }" class="flex flex-col gap-2 p-4 bg-neutral-50 rounded-[10px] border border-neutral-200">
                    <p class="text-sm font-semibold text-neutral-600">Lokasi montir datang</p>
                    <div wire:ignore>
                        <div id="booking-map" x-init="bookingMap($el, $wire)"
                            data-center-lat="-7.7174465" data-center-lng="113.0755426"
                            class="w-full h-64 rounded-lg z-0"></div>
                    </div>
                    <button type="button" x-bind:disabled="locating"
                        x-on:click="locateError = ''; document.getElementById('booking-map').pinMap.locate({
                            onStart: () => locating = true,
                            onDone: () => locating = false,
                            onError: (msg) => locateError = msg,
                        })"
                        class="px-4 py-3 bg-white border border-neutral-200 rounded-[10px] text-sm font-semibold text-neutral-700 disabled:opacity-60">
                        <span x-show="!locating">Lokasi saya</span>
                        <span x-show="locating" style="display:none">Mencari lokasi...</span>
                    </button>
                    <p class="text-sm text-red-600" style="display:none" x-show="locateError" x-text="locateError"></p>
                    <p class="text-sm text-neutral-500" x-show="$wire.latitude == null">
                        Ketuk peta untuk menaruh pin di lokasi kamu. Pin bisa digeser.
                    </p>
                    <p class="text-sm font-semibold text-green-700" style="display:none" x-show="$wire.latitude != null">
                        Lokasi sudah dipilih. Geser pin kalau kurang pas.
                    </p>
                    @error('latitude') <small class="text-sm text-red-600">{{ $message }}</small> @enderror

                    <input type="text" wire:model="address" maxlength="255"
                        placeholder="Patokan (opsional), contoh: depan Indomaret, pagar hijau"
                        class="w-full p-4 bg-white rounded-[10px] text-neutral-800 placeholder:text-neutral-400 border border-neutral-200 outline-none focus:ring-2 focus:ring-red-500">
                    @error('address') <small class="text-sm text-red-600">{{ $message }}</small> @enderror
                </div>
            @endif

            @error('throttle') <small class="text-sm text-red-600">{{ $message }}</small> @enderror

            <button type="submit" wire:loading.attr="disabled"
                class="px-6 py-3.5 bg-gradient-to-b from-red-500 to-red-600 rounded-[10px] text-white font-semibold flex justify-center items-center gap-4 disabled:opacity-60">
                <span wire:loading.remove>Booking Sekarang &rarr;</span>
                <span wire:loading>Mengirim...</span>
            </button>
        </form>
    @endif
</div>