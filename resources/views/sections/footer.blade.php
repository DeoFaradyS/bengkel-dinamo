<footer class="bg-neutral-900 text-white py-16">
  <div class="max-w-[1200px] mx-auto px-6 grid grid-cols-2 lg:grid-cols-3 gap-10">

    <div class="col-span-2 lg:col-span-1">
      <a href="/" aria-label="Beranda" class="inline-block">
        <img src="{{ asset('images/logo-dinamo-putih.png') }}" alt="Dinamo, Bengkel Cak Budi" width="787" height="200"
          class="h-10 w-auto">
      </a>
      <p class="text-neutral-500 text-sm mt-4">© {{ date('Y') }} Budi Dinamo. Hak cipta dilindungi.</p>
    </div>

    <nav>
      <p class="text-neutral-500 text-sm mb-3">Navigasi</p>
      <ul class="space-y-2 text-sm">
        <li><a href="/" class="hover:text-neutral-300">Beranda</a></li>
        <li><a href="/#layanan" class="hover:text-neutral-300">Layanan</a></li>
        <li><a href="/#lokasi" class="hover:text-neutral-300">Lokasi</a></li>
        <li><a href="/#cek-booking" class="hover:text-neutral-300">Cek Booking</a></li>
      </ul>
    </nav>

    <div>
      <p class="text-neutral-500 text-sm mb-3">Kontak</p>
      <ul class="space-y-2 text-sm">
        <li><a href="tel:+628882509745" class="hover:text-neutral-300">+62 888-2509-745</a></li>
        {{-- TODO: ganti dengan email asli bengkel, atau hapus baris ini --}}
        <li><a href="mailto:info@budidinamo.com" class="hover:text-neutral-300">info@budidinamo.com</a></li>
        <li>Nguling, Pasuruan, Jawa Timur</li>
        <li>Senin–Sabtu: 08:00 — 17:00</li>
      </ul>
    </div>

  </div>
</footer>
