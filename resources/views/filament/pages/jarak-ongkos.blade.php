<x-filament-panels::page>
        <style>
        /* Dua kartu satu baris sama tinggi: kartu mengisi penuh selnya. */
        .jo-grid { align-items: stretch; }
        .jo-grid > :has(> .fi-section) { display: flex; flex-direction: column; }
        .jo-grid > :has(> .fi-section) > .fi-section { flex: 1 1 auto; }

        /* Isi kartu Pratinjau mengisi sisa tinggi; peta menempel penuh di dalamnya. */
        .jo-preview { display: flex; flex-direction: column; }
        .jo-preview .fi-section-content-ctn { flex: 1 1 auto; position: relative; min-height: 260px; }
        .jo-map-fill { position: absolute; inset: 1.5rem; }
    </style>

    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
            <x-filament::button type="submit">Simpan Perubahan</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>