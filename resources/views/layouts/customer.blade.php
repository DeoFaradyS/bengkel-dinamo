@php
    $user = auth()->user();
    $base = 'flex items-center gap-3 whitespace-nowrap rounded-[10px] px-4 py-3 text-sm font-semibold transition-colors';
    $on = 'bg-red-50 text-red-600';
    $off = 'text-neutral-600 hover:bg-neutral-100 hover:text-red-600';
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Akun Saya - Budi Dinamo')</title>

    @fonts

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles
</head>

<body class="bg-neutral-50 text-neutral-800 antialiased">
    @include('sections.navbar')

    <div class="mx-auto flex min-h-[calc(100dvh-6rem)] w-full max-w-[1200px] flex-col gap-6 px-4 py-8 lg:flex-row lg:gap-8 lg:py-10 xl:px-1">

        {{-- Sidebar. Di HP jadi baris menu geser ke samping. --}}
                <aside
            class="w-full shrink-0 rounded-[10px] border border-neutral-200 bg-white p-3 lg:sticky lg:top-28 lg:w-[280px] lg:p-6">

            <div class="hidden flex-col gap-3 pb-6 lg:flex">
                <span class="flex size-14 items-center justify-center rounded-full bg-red-600 text-xl font-bold text-white"
                    aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="truncate text-base font-bold uppercase text-neutral-800">{{ $user->name }}</p>
                    <p class="text-sm text-neutral-500">Pelanggan</p>
                </div>
            </div>

            <nav class="flex gap-1 overflow-x-auto lg:flex-col lg:overflow-visible" aria-label="Menu akun">
                <a href="{{ route('riwayat') }}" class="{{ $base }} {{ request()->routeIs('riwayat') ? $on : $off }}"
                    @if (request()->routeIs('riwayat')) aria-current="page" @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0"
                        aria-hidden="true">
                        <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                    </svg>
                    Riwayat Booking
                </a>

                <a href="{{ route('profil') }}" class="{{ $base }} {{ request()->routeIs('profil') ? $on : $off }}"
                    @if (request()->routeIs('profil')) aria-current="page" @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0"
                        aria-hidden="true">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    Profil
                </a>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            @yield('content')
        </div>
    </div>

    @include('sections.footer')

    @include('sections.wa-float')

    @livewireScripts
</body>

</html>