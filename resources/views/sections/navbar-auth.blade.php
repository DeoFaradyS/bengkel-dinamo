@php($mobile = $mobile ?? false)
@php($wrap = $mobile ? 'flex flex-col gap-5' : 'flex items-center gap-4')

<div class="{{ $wrap }}">
  @auth
    <span class="text-sm font-semibold text-neutral-800 normal-case">{{ auth()->user()->name }}</span>

    @if (auth()->user()->role === \App\Enums\UserRole::Owner)
      <a href="/admin" class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Panel</a>
    @endif

    <a href="/riwayat" class="text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Riwayat Saya</a>

    <form method="POST" action="/keluar">
      @csrf
      <button type="submit"
        class="text-sm font-semibold uppercase text-neutral-800 transition-colors hover:text-red-600">Keluar</button>
    </form>
  @else
    <a href="{{ route('login') }}"
      class="{{ $mobile ? '' : 'px-4 py-3 ' }}text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Masuk</a>
    <a href="{{ route('register') }}"
      class="{{ $mobile ? 'text-red-600' : 'px-6 py-3 bg-neutral-800 rounded-[10px] text-white transition-colors hover:bg-neutral-700' }} font-semibold">Daftar</a>
  @endauth
</div>