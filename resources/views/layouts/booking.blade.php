<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Budi Dinamo - Servis Dinamo & Kelistrikan Mobil di Nguling')</title>

    @fonts

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles
</head>

<body class="bg-white text-neutral-800 antialiased">
    @include('sections.navbar')

    @isset($slot)
        {{-- Halaman Livewire penuh, misalnya /booking --}}
        <main class="max-w-[560px] mx-auto px-6 py-16">
            <h1 class="text-4xl font-bold text-neutral-800">Booking Servis</h1>
            <p class="mt-3 mb-8 text-neutral-500">Isi data di bawah. Kami hubungi kamu untuk konfirmasi jadwal.</p>
            <div class="p-8 bg-white rounded-[10px] shadow-[0px_0px_60px_0px_rgba(0,0,0,0.08)]">
                {{ $slot }}
            </div>
        </main>
    @else
        @yield('content')
    @endisset

    @include('sections.footer')

    @livewireScripts
</body>

</html>
