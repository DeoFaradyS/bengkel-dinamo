{{-- Lokasi Panggil Montir: klik peta, geser pin, atau cari nama tempat. Koordinat tersimpan otomatis. --}}
<div class="flex flex-col gap-2">
    <div>
        <p class="text-sm font-medium text-gray-950">Lokasi di peta<sup class="text-danger-600 font-medium">*</sup></p>
        <p class="text-sm text-gray-500">Cari nama desa atau jalan, atau klik peta. Pin bisa digeser.</p>
    </div>

    <div wire:ignore>
        <div x-data
            x-init="window.adminPinMap
                ? window.adminPinMap($el, $wire)
                : window.addEventListener('admin-map-ready', () => window.adminPinMap($el, $wire), { once: true })"
            class="w-full rounded-lg"
            style="height: 320px; position: relative; z-index: 0;"></div>
    </div>

    @error('data.latitude')
        <p class="text-sm text-danger-600">{{ $message }}</p>
    @enderror
</div>
