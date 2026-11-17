<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Budi Dinamo')</title>

    @fonts

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles
</head>

<body class="min-h-dvh flex items-start sm:items-center justify-center bg-neutral-50 text-neutral-800 antialiased px-4 py-6 sm:py-10">
    <main class="w-full max-w-[480px]">
        {{-- Jarak ke judul 24px (kelipatan 8). --}}
        <a href="/" aria-label="Kembali ke beranda"
            class="mb-6 inline-flex items-center gap-2 py-2 text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true">
                <path d="M19 12H5" />
                <path d="m12 19-7-7 7-7" />
            </svg>
            Beranda
        </a>

        @yield('content')
    </main>

    {{-- Livewire memuat Alpine, dipakai tombol lihat sandi. --}}
    @livewireScripts
</body>

</html>