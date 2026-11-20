{{-- Header halaman Laporan: breadcrumb dan judul di kiri, filter periode sejajar tombol Unduh PDF di kanan (aturan 30, 31). --}}
<header class="fi-header fi-header-has-breadcrumbs">
    <div>
        @if (filament()->hasBreadcrumbs())
            <x-filament::breadcrumbs :breadcrumbs="$page->getBreadcrumbs()" />
        @endif

        <h1 class="fi-header-heading">{{ $page->getHeading() }}</h1>
    </div>

    <div class="fi-header-actions-ctn" style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <div style="flex:none;">
            {!! $page->getFiltersForm()->toEmbeddedHtml() !!}
        </div>
        <x-filament::actions :actions="$page->getCachedHeaderActions()" />
    </div>
</header>
