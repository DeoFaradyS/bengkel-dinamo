@php
    $page = $getLivewire();
@endphp

{{-- Judul bagian Kinerja periode, filter tanggal tepat di atas KPI dan grafik yang ikut filter. --}}
<div style="display:flex;align-items:center;gap:.75rem 1rem;width:100%;margin-top:1rem;flex-wrap:wrap;">
    <h2 class="text-lg font-semibold text-gray-950" style="flex:none;">Kinerja periode</h2>

    <div style="flex:1;min-width:0;display:flex;justify-content:flex-end;">
        <div style="flex:none;">
            {!! $page->getFiltersForm()->toEmbeddedHtml() !!}
        </div>
    </div>
</div>
