@php
  $mobile = $mobile ?? false;
  $wrap = $mobile ? 'flex flex-col gap-5' : 'flex items-center gap-4';

  $user = auth()->user();
  $isOwner = $user?->role === \App\Enums\UserRole::Owner;
  $firstName = $user ? \Illuminate\Support\Str::of($user->name)->before(' ') : '';
  $initial = $user ? mb_strtoupper(mb_substr($user->name, 0, 1)) : '';
  $item = 'flex w-full items-center px-4 py-2.5 text-left text-sm font-semibold normal-case text-neutral-800 transition-colors hover:bg-neutral-100 hover:text-red-600';
@endphp

<div class="{{ $wrap }}">
  @auth
    @if ($mobile)
      {{-- HP: daftar biasa di dalam menu lipat, tanpa dropdown. --}}
      <span class="text-sm font-semibold normal-case text-neutral-800">{{ $user->name }}</span>

      @if ($isOwner)
        <a href="/admin" class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Dashboard</a>
        <a href="/admin/services"
          class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Servis</a>
        <a href="/admin/spare-parts"
          class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Suku Cadang</a>
        <a href="/admin/service-types"
          class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Jasa</a>
        <a href="/admin/categories"
          class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Kategori</a>
        <a href="/admin/jarak-ongkos"
          class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Jarak Ongkos</a>
      @else
        <a href="{{ route('riwayat', absolute: false) }}"
          class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Riwayat Booking</a>
        <a href="{{ route('profil', absolute: false) }}"
          class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Profil</a>
      @endif

      <form method="POST" action="/keluar">
        @csrf
        <button type="submit"
          class="text-sm font-semibold uppercase text-neutral-800 transition-colors hover:text-red-600">Keluar</button>
      </form>
    @else
      {{-- Desktop: tombol avatar + nama depan, klik buka dropdown. --}}
      <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false"
        x-on:keydown.escape.window="open = false">
        <button type="button" x-on:click="open = !open" aria-haspopup="true" x-bind:aria-expanded="open"
          class="flex items-center gap-2 rounded-[10px] py-1 pl-1 pr-2 transition-colors hover:bg-neutral-100">
          <span class="flex size-9 items-center justify-center rounded-full bg-red-600 text-sm font-bold text-white"
            aria-hidden="true">{{ $initial }}</span>
          <span class="max-w-[120px] truncate text-sm font-semibold normal-case text-neutral-800">{{ $firstName }}</span>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" class="size-4 text-neutral-500 transition-transform"
            x-bind:class="open ? 'rotate-180' : ''" aria-hidden="true">
            <path d="m6 9 6 6 6-6" />
          </svg>
        </button>

        <div x-cloak x-show="open" x-transition.origin.top.right
          class="absolute right-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-[10px] border border-neutral-200 bg-white py-2 shadow-[0px_8px_30px_0px_rgba(0,0,0,0.10)]">
          <div class="border-b border-neutral-200 px-4 pb-3 pt-1">
            <p class="truncate text-sm font-semibold normal-case text-neutral-800">{{ $user->name }}</p>
            <p class="truncate text-xs normal-case text-neutral-500">{{ $user->email }}</p>
          </div>

          <div class="mt-2">
            @if ($isOwner)
              <a href="/admin" class="{{ $item }}">Dashboard</a>
              <a href="/admin/services" class="{{ $item }}">Servis</a>
              <a href="/admin/spare-parts" class="{{ $item }}">Suku Cadang</a>
              <a href="/admin/service-types" class="{{ $item }}">Jasa</a>
              <a href="/admin/categories" class="{{ $item }}">Kategori</a>
              <a href="/admin/jarak-ongkos" class="{{ $item }}">Jarak Ongkos</a>
            @else
              <a href="{{ route('riwayat', absolute: false) }}" class="{{ $item }}">Riwayat Booking</a>
              <a href="{{ route('profil', absolute: false) }}" class="{{ $item }}">Profil</a>
            @endif
          </div>

          <form method="POST" action="/keluar" class="mt-1 border-t border-neutral-200 pt-1">
            @csrf
            <button type="submit" class="{{ $item }}">Keluar</button>
          </form>
        </div>
      </div>
    @endif
    @else
    @php
      $btn = 'inline-flex h-11 items-center justify-center rounded-[10px] px-5 text-sm font-semibold uppercase transition-colors';
    @endphp

    <div class="{{ $mobile ? 'grid grid-cols-2 gap-3 border-t border-neutral-200 pt-5' : 'flex items-center gap-2' }}">
      <a href="{{ route('login') }}"
        class="{{ $btn }} border border-neutral-300 text-neutral-800 hover:border-red-600 hover:text-red-600">Masuk</a>
      <a href="{{ route('register') }}"
        class="{{ $btn }} border border-neutral-800 bg-neutral-800 text-white hover:border-neutral-700 hover:bg-neutral-700">Daftar</a>
    </div>
  @endauth
</div>