@extends('layouts.booking')

@section('title', 'Halaman tidak ditemukan - Budi Dinamo')

{{-- Halaman 404 untuk semua halaman yang tidak ada (Laravel otomatis memakai file ini). --}}
@section('content')
<section class="max-w-[1200px] mx-auto px-6 py-24 flex flex-col items-center gap-6 text-center">
  <p class="text-7xl font-bold text-red-600">404</p>

  <div class="flex flex-col gap-3">
    <h1 class="text-3xl lg:text-4xl font-semibold text-neutral-800">Halaman tidak ditemukan</h1>
    <p class="max-w-md text-lg text-neutral-500">
      Halaman yang kamu cari tidak ada atau sudah tidak tersedia.
    </p>
  </div>

  <div class="flex flex-col sm:flex-row gap-3">
    <a href="{{ url('/') }}"
      class="px-6 py-3.5 bg-gradient-to-b from-red-500 to-red-600 rounded-[10px] text-white font-semibold">
      Kembali ke Beranda
    </a>
    <a href="{{ url('/layanan') }}"
      class="px-6 py-3.5 bg-white border border-neutral-200 rounded-[10px] text-neutral-700 font-semibold hover:border-neutral-300">
      Lihat Layanan
    </a>
  </div>
</section>
@endsection
