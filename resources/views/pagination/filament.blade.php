@php
    $btn = 'flex size-9 items-center justify-center rounded-lg text-sm font-semibold transition-colors';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginasi" class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="{{ $btn }} cursor-not-allowed text-neutral-300" aria-disabled="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="m15 18-6-6 6-6" /></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" class="{{ $btn }} text-neutral-600 hover:bg-neutral-100">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="m15 18-6-6 6-6" /></svg>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="{{ $btn }} text-neutral-400">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="{{ $btn }} bg-red-50 text-red-600">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Halaman {{ $page }}" class="{{ $btn }} text-neutral-600 hover:bg-neutral-100">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya" class="{{ $btn }} text-neutral-600 hover:bg-neutral-100">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
            </a>
        @else
            <span class="{{ $btn }} cursor-not-allowed text-neutral-300" aria-disabled="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
            </span>
        @endif
    </nav>
@endif