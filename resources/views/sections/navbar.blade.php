@php
  $waLink = \App\Support\WhatsAppLink::make('08882509745', 'Halo Budi Dinamo, saya mau tanya soal servis.');
@endphp

{{-- Jarak scroll untuk header sticky, supaya judul section tidak tertutup saat tautan #anchor diklik --}}
<style>
  html {
    scroll-padding-top: 6rem;
  }
</style>

<header class="w-full flex justify-center py-5 bg-white sticky top-0 z-50">
  <div class="w-full max-w-[1200px] px-1">

    <div class="flex items-center lg:gap-16">

      <a href="/" class="flex items-center text-2xl font-bold text-neutral-900" aria-label="Beranda">
        Budi<span class="text-red-600">Dinamo</span>
      </a>

      <ul class="hidden lg:flex items-center gap-10 text-sm font-semibold uppercase text-neutral-800">
        <li><a href="/#layanan">Layanan</a></li>
        <li><a href="/#lokasi">Lokasi</a></li>
        <li><a href="/#galeri">Cek Booking</a></li>
      </ul>

      <div class="hidden lg:flex items-center gap-4 ml-auto">
        {{-- PLACEHOLDER: belum ada fungsi. Menunggu keputusan poin 10 (login pelanggan). Ganti atau hapus sebelum
        sidang. --}}
        <button type="button"
          class="px-4 py-3 text-sm font-semibold text-neutral-800 transition-colors hover:text-red-600">Masuk</button>
        <button type="button"
          class="px-6 py-3 bg-neutral-800 rounded-[10px] text-white font-semibold transition-colors hover:bg-neutral-700">Daftar</button>
      </div>

      <button type="button" id="menuBtn" class="lg:hidden size-11 flex items-center justify-center ml-auto"
        aria-expanded="false" aria-controls="mobileMenu" aria-label="Buka menu">
        <span class="relative flex flex-col justify-center w-5 h-4">
          <span id="bar1" class="absolute w-5 h-0.5 rounded-full bg-red-600 transition-transform duration-300"></span>
          <span id="bar2" class="absolute w-5 h-0.5 rounded-full bg-red-600 transition-all duration-300"
            style="top: 100%"></span>
        </span>
      </button>

    </div>

    <div id="mobileMenu"
      class="lg:hidden grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out">
      <div class="overflow-hidden">
        <div class="mt-5 flex flex-col gap-5 text-sm font-semibold uppercase text-neutral-800 pb-2">
          <a href="/#layanan">Layanan</a>
          <a href="/#galeri">Galeri</a>
          <a href="/#lokasi">Lokasi</a>
          <a href="{{ $waLink }}" target="_blank" rel="noopener" class="text-red-600">+62 888-2509-745</a>
          <a href="/booking"
            class="flex items-center justify-center gap-4 px-6 py-3.5 bg-neutral-800 rounded-[10px] text-white normal-case transition-colors hover:bg-neutral-700">
            Booking Servis
          </a>
        </div>
      </div>
    </div>

  </div>
</header>

<script>
  (() => {
    const btn = document.getElementById('menuBtn');
    const menu = document.getElementById('mobileMenu');
    const bar1 = document.getElementById('bar1');
    const bar2 = document.getElementById('bar2');

    const setOpen = (isOpen) => {
      menu.classList.toggle('grid-rows-[1fr]', isOpen);
      menu.classList.toggle('grid-rows-[0fr]', !isOpen);
      btn.setAttribute('aria-expanded', String(isOpen));
      bar1.style.transform = isOpen ? 'rotate(45deg) translateY(4px)' : '';
      bar2.style.top = isOpen ? '4px' : '100%';
      bar2.style.transform = isOpen ? 'rotate(-45deg)' : '';
    };

    btn.addEventListener('click', () => {
      setOpen(btn.getAttribute('aria-expanded') !== 'true');
    });

    // Menu menutup sendiri setelah satu tautan ditekan.
    menu.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setOpen(false)));
  })();
</script>