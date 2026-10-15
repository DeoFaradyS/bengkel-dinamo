@php
    $images = [
        'https://placehold.co/500x300?text=Foto+Bengkel+1',
        'https://placehold.co/500x300?text=Foto+Bengkel+2',
        'https://placehold.co/500x300?text=Foto+Bengkel+3',
        'https://placehold.co/500x300?text=Foto+Bengkel+4',
        'https://placehold.co/500x300?text=Foto+Bengkel+5',
    ];
@endphp

<section id="galeri" class="p-10 overflow-hidden">
    <div class="flex gap-5 w-max" style="animation: marquee 20s linear infinite;">
        @foreach ([...$images, ...$images] as $src)
            <img src="{{ $src }}" loading="lazy" alt="Galeri Bengkel Budi Dinamo"
                class="h-72 w-auto rounded-3xl object-cover shrink-0" />
        @endforeach
    </div>
</section>

<style>
    @keyframes marquee {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }
</style>