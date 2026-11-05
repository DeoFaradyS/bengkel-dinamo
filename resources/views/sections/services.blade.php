{{--
Section Layanan di beranda. Isi dibaca dari service_types.
Variabel yang dipakai:
$services maksimal 6 layanan untuk ditampilkan
$totalCount jumlah seluruh layanan; kalau lebih besar dari yang tampil, tombol "Lihat semua layanan" muncul
Kalau variabel belum dikirim, section tampil kosong (bukan error).
--}}
@php
  $services = $services ?? collect();
  $totalCount = $totalCount ?? $services->count();
@endphp

<section id="layanan" class="max-w-[1200px] mx-auto px-1 flex flex-col items-center gap-12 py-20">

  <div class="flex flex-col items-center gap-6 text-center">
    <div>
      <h2 class="max-w-[700px] text-5xl lg:text-6xl font-semibold leading-tight">Layanan Bengkel Kami</h2>
      <p class="max-w-full mt-3.5 text-neutral-500">Servis dinamo starter, alternator, dan kelistrikan mobil.</p>
    </div>
  </div>

  @if ($services->isEmpty())
    <p class="max-w-[480px] text-center text-neutral-500">
      Daftar layanan belum tersedia. Tanya layanan dan harga lewat WhatsApp.
    </p>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 w-full">
      @foreach ($services as $service)
        <a href="{{ url('/layanan/' . $service->id) }}"
          class="block focus:outline-none focus:ring-2 focus:ring-red-500 rounded-[10px]">
          <x-service-card :name="$service->name" :description="$service->description" :price="$service->default_price"
            :photo-url="$service->photo_url" />
        </a>
      @endforeach
    </div>

    @if ($totalCount > $services->count())
      <a href="{{ url('/layanan') }}"
        class="px-6 py-3 bg-neutral-800 rounded-[10px] text-white font-semibold transition-colors hover:bg-neutral-700">
        Lihat semua layanan
      </a>
    @endif
  @endif

</section>