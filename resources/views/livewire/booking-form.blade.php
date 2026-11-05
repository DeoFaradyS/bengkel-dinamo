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
                            <input type="radio" wire:model.live="booking_type" value="{{ $type->value }}" class="sr-only">
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

            <div class="flex flex-col gap-2">
                <div wire:ignore>
                    <input type="text" id="slot-date" readonly x-init="bookingDate($el, $wire)"
                        data-value="{{ $slot_date }}" data-min="{{ now()->format('Y-m-d') }}"
                        data-max="{{ now()->addDays((int) config('booking.max_days_ahead'))->format('Y-m-d') }}"
                        class="w-full p-4 bg-neutral-100 rounded-[10px] text-neutral-800 outline-none cursor-pointer focus:ring-2 focus:ring-red-500">
                </div>

                {{-- Jam yang sudah lewat disembunyikan. Jam yang sudah dipesan orang lain tetap tampil, dicoret. --}}
                @php $slotTampil = $daftarSlot->reject(fn ($s) => $s['time']->isPast()); @endphp

                @if ($slotTampil->isEmpty())
                    <p class="text-sm text-neutral-500">Tidak ada jam tersedia di tanggal ini. Pilih tanggal lain.</p>
                @else
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                        @foreach ($slotTampil as $item)
                            @php $value = $item['time']->format('Y-m-d H:i'); @endphp
                            <button type="button" wire:key="slot-{{ $value }}" wire:click="pilihSlot('{{ $value }}')"
                                @disabled(!$item['available'])
                                class="py-3 rounded-[10px] text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-red-500 {{ $scheduled_at === $value ? 'bg-red-600 text-white' : ($item['available'] ? 'bg-neutral-100 text-neutral-800' : 'bg-neutral-100 text-neutral-300 line-through cursor-not-allowed') }}">
                                {{ $item['time']->format('H.i') }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @error('scheduled_at') <small class="text-sm text-red-600">{{ $message }}</small> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <textarea wire:model="complaint" placeholder="Keluhan" rows="2"
                    class="w-full p-4 bg-neutral-100 rounded-[10px] text-neutral-800 placeholder:text-neutral-400 outline-none focus:ring-2 focus:ring-red-500"></textarea>
                @error('complaint') <small class="text-sm text-red-600">{{ $message }}</small> @enderror
            </div>

            @if ($booking_type === \App\Enums\BookingType::HomeService->value)
                <div x-data="{
                        locating: false,
                        locateError: '',
                        mapOpen: false,
                        openMap() {
                            this.mapOpen = true;
                            // Leaflet perlu tahu ukuran baru setelah modal tampil, kalau tidak peta abu-abu.
                            this.$nextTick(() => document.getElementById('booking-map').pinMap.map.invalidateSize());
                        },
                        locate() {
                            this.locateError = '';
                            document.getElementById('booking-map').pinMap.locate({
                                onStart: () => this.locating = true,
                                onDone: () => this.locating = false,
                                onError: (msg) => this.locateError = msg,
                            });
                        },
                    }" x-effect="document.body.style.overflow = mapOpen ? 'hidden' : ''"
                    x-on:keydown.escape.window="mapOpen = false"
                    class="flex flex-col gap-3 p-4 bg-neutral-50 rounded-[10px] border border-neutral-200">
                    <p class="text-sm font-semibold text-neutral-600">Lokasi montir datang</p>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="button" x-on:click="openMap()"
                            class="flex-1 px-4 py-3 bg-white border border-neutral-200 rounded-[10px] text-sm font-semibold text-neutral-700 hover:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <span x-text="$wire.latitude == null ? 'Pilih di peta' : 'Ubah di peta'">Pilih di peta</span>
                        </button>
                        <button type="button" x-bind:disabled="locating" x-on:click="locate()"
                            class="flex-1 px-4 py-3 bg-white border border-neutral-200 rounded-[10px] text-sm font-semibold text-neutral-700 hover:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-60">
                            <span x-show="!locating">Pakai lokasi saya</span>
                            <span x-show="locating" style="display:none">Mencari lokasi...</span>
                        </button>
                    </div>

                    <p class="text-sm font-semibold text-green-700" style="display:none"
                        x-show="$wire.latitude != null">Lokasi sudah dipilih</p>

                    <p class="text-sm text-red-600" style="display:none" x-show="locateError" x-text="locateError"></p>

                    <p class="text-sm text-neutral-500" style="display:none" wire:loading.inline wire:target="hitungJarak">
                        Menghitung jarak...
                    </p>
                    @if ($distanceText)
                        <p class="text-sm font-semibold text-neutral-700" wire:loading.remove wire:target="hitungJarak">
                            {{ $distanceText }}
                        </p>
                    @endif
                    @if ($distanceError)
                        <p class="text-sm font-semibold text-red-600" wire:loading.remove wire:target="hitungJarak">
                            {{ $distanceError }}
                        </p>
                    @endif
                    @error('latitude') <small class="text-sm text-red-600">{{ $message }}</small> @enderror

                    <input type="text" wire:model="address" maxlength="255"
                        placeholder="Patokan (opsional), contoh: depan Indomaret, pagar hijau"
                        class="w-full p-4 bg-white rounded-[10px] text-neutral-800 placeholder:text-neutral-400 border border-neutral-200 outline-none focus:ring-2 focus:ring-red-500">
                    @error('address') <small class="text-sm text-red-600">{{ $message }}</small> @enderror

                    {{-- Peta di modal: penuh layar di ponsel, kotak di desktop. --}}
                    <div x-show="mapOpen" style="display:none"
                        class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/50 sm:p-6"
                        role="dialog" aria-modal="true" aria-label="Pilih lokasi montir datang">
                        <div class="flex flex-col gap-3 w-full h-full sm:h-auto sm:max-w-xl p-4 sm:p-6 bg-white sm:rounded-2xl">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-neutral-800">Pilih lokasi montir datang</p>
                                <button type="button" x-on:click="mapOpen = false" aria-label="Tutup peta"
                                    class="size-10 flex items-center justify-center rounded-lg text-2xl text-neutral-500 hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500">&times;</button>
                            </div>

                            <p class="text-sm text-neutral-500">Ketuk peta untuk menaruh pin. Pin bisa digeser.</p>

                            <div wire:ignore class="flex-1 min-h-0 sm:flex-none">
                                <div id="booking-map" x-init="bookingMap($el, $wire)" data-center-lat="-7.7174465"
                                    data-center-lng="113.0755426"
                                    class="w-full h-full min-h-72 sm:h-96 rounded-lg z-0"></div>
                            </div>

                            <p class="text-sm text-neutral-500" style="display:none" wire:loading.inline wire:target="hitungJarak">
                                Menghitung jarak...
                            </p>
                            @if ($distanceText)
                                <p class="text-sm font-semibold text-neutral-700" wire:loading.remove wire:target="hitungJarak">
                                    {{ $distanceText }}
                                </p>
                            @endif
                            @if ($distanceError)
                                <p class="text-sm font-semibold text-red-600" wire:loading.remove wire:target="hitungJarak">
                                    {{ $distanceError }}
                                </p>
                            @endif

                            <button type="button" x-bind:disabled="$wire.latitude == null" x-on:click="mapOpen = false"
                                class="px-6 py-3.5 bg-neutral-800 rounded-[10px] text-white font-semibold transition-colors hover:bg-neutral-700 disabled:opacity-60">
                                Selesai
                            </button>
                        </div>
                    </div>
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