@php
    $page = $getLivewire();
@endphp

<div style="display:flex;flex-direction:column;gap:.25rem;">
    <h1 class="fi-header-heading">{{ $page->getTitle() }}</h1>
    <h2 class="text-lg font-semibold text-gray-950" style="margin-top:.75rem;">Hari ini</h2>
</div>
