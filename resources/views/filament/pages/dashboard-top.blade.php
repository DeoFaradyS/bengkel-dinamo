@php
    $page = $getLivewire();
@endphp

<div style="display:flex;align-items:center;gap:1rem;width:100%;">
    <h1 class="fi-header-heading" style="flex:none;">{{ $page->getTitle() }}</h1>

    <div style="flex:1;min-width:0;display:flex;justify-content:flex-end;">
        <div style="flex:none;">
            {!! $page->getFiltersForm()->toEmbeddedHtml() !!}
        </div>
    </div>
</div>