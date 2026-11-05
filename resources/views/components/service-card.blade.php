{{--
    Kartu satu layanan. Hanya tampilan, bukan tautan (tidak ada halaman detail per layanan).
    Dipakai di sections/services.blade.php dan nanti di halaman /layanan.

    Props:
      name        nama layanan (wajib)
      description teks singkat, boleh kosong
      price       harga dasar (default_price); kosong atau 0 tampil "Hubungi kami"
      photoUrl    URL foto; kosong tampil kotak abu berisi inisial
--}}
@props([
    'name',
    'description' => null,
    'price' => null,
    'photoUrl' => null,
])

@php
    // Inisial dari dua kata pertama: "Servis Dinamo Starter" jadi "SD".
    $initials = collect(preg_split('/\s+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');

    // default_price bisa berupa string desimal ("150000.00"), jadi dicek lewat is_numeric.
    $hasPrice = is_numeric($price) && (float) $price > 0;
@endphp

<article class="flex flex-col overflow-hidden rounded-3xl border border-neutral-200 bg-white">
    @if ($photoUrl)
        <img src="{{ $photoUrl }}" alt="{{ $name }}" loading="lazy"
             class="aspect-[4/3] w-full object-cover">
    @else
        <div class="flex aspect-[4/3] w-full items-center justify-center bg-neutral-100" aria-hidden="true">
            <span class="text-5xl font-semibold text-neutral-400">{{ $initials }}</span>
        </div>
    @endif

    <div class="flex grow flex-col gap-2 p-5">
        <h3 class="text-lg font-semibold leading-snug">{{ $name }}</h3>

        @if (filled($description))
            <p data-service-description class="line-clamp-2 text-sm text-neutral-500">{{ $description }}</p>
        @endif

        <p class="mt-auto pt-2">
            @if ($hasPrice)
                <span class="block text-sm text-neutral-500">Mulai dari</span>
                <span class="text-lg font-semibold">Rp {{ number_format((float) $price, 0, ',', '.') }}</span>
            @else
                <span class="text-lg font-semibold">Hubungi kami</span>
            @endif
        </p>
    </div>
</article>