<section id="appointment" class="pt-16 pb-16 lg:pb-20">
  <div
    class="w-full max-w-[1200px] mx-auto px-1 grid gap-8 lg:gap-x-12 lg:gap-y-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,500px)] lg:grid-rows-[auto_1fr]">

    <!-- Judul + checklist -->
    <div class="w-full lg:col-start-1 lg:row-start-1 flex flex-col gap-8">
      <div>
        <h1 class="text-4xl lg:text-6xl font-bold leading-tight">
          <span class="text-neutral-800">Bengkel Dinamo <br>Terpercaya di </span><span
            class="text-red-600 underline decoration-4 underline-offset-8">Nguling.</span>
        </h1>
        <p class="mt-5 max-w-[46ch] text-neutral-500 text-base font-medium">
          Budi Dinamo melayani servis dinamo starter, alternator, dan kelistrikan mobil
          dengan teknisi berpengalaman.
        </p>
      </div>

      <ul class="flex flex-wrap gap-x-8 gap-y-3">
        @foreach (['Sparepart Original', 'Garansi Servis', 'Pelayanan Ramah'] as $item)
          <li class="flex items-center gap-3">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"
              aria-hidden="true">
              <path
                d="M14.3535 4.85378L6.35354 12.8538C6.3071 12.9003 6.25196 12.9372 6.19126 12.9623C6.13056 12.9875 6.0655 13.0004 5.99979 13.0004C5.93408 13.0004 5.86902 12.9875 5.80832 12.9623C5.74762 12.9372 5.69248 12.9003 5.64604 12.8538L2.14604 9.35378C2.05222 9.25996 1.99951 9.13272 1.99951 9.00003C1.99951 8.86735 2.05222 8.7401 2.14604 8.64628C2.23986 8.55246 2.36711 8.49976 2.49979 8.49976C2.63247 8.49976 2.75972 8.55246 2.85354 8.64628L5.99979 11.7932L13.646 4.14628C13.7399 4.05246 13.8671 3.99976 13.9998 3.99976C14.1325 3.99976 14.2597 4.05246 14.3535 4.14628C14.4474 4.2401 14.5001 4.36735 14.5001 4.50003C14.5001 4.63272 14.4474 4.75996 14.3535 4.85378Z"
                fill="#E81C2E" />
            </svg>
            <span class="text-neutral-800 text-base font-medium">{{ $item }}</span>
          </li>
        @endforeach
      </ul>
    </div>

    <!-- Teks + form booking (teks sudah pindah ke kolom kiri) -->
    <div
      class="w-full lg:col-start-2 lg:row-start-1 lg:row-span-2 p-6 sm:p-8 bg-white rounded-[10px] border border-neutral-100 shadow-[0px_10px_40px_0px_rgba(0,0,0,0.08)]">
      <livewire:booking-form />
    </div>

    <div class="relative lg:col-start-1 lg:row-start-2 min-h-56 lg:min-h-64 rounded-[10px] overflow-hidden">
      <img src="{{ asset('images/bengkel.png') }}" alt="Bengkel Budi Dinamo"
        decoding="async" class="absolute inset-0 size-full object-cover">
    </div>
</section>