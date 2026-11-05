@extends('layouts.booking')

@section('title', $service->name)

@section('content')
<section class="max-w-[1200px] mx-auto px-1 py-20 flex flex-col gap-8">

  <a href="{{ url('/layanan') }}" class="text-sm font-semibold text-neutral-500 hover:text-neutral-800">
    &larr; Semua layanan
  </a>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
    <div class="w-full aspect-[4/3] overflow-hidden rounded-2xl bg-neutral-100">
      @if ($service->photo_url)
        <img src="{{ $service->photo_url }}" alt="{{ $service->name }}" class="w-full h-full object-cover">
      @else
        <div class="w-full h-full flex items-center justify-center text-neutral-400">Foto belum tersedia</div>
      @endif
    </div>

    <div class="flex flex-col gap-6">
      <h1 class="text-4xl lg:text-5xl font-semibold leading-tight">{{ $service->name }}</h1>

      <div>
        <p class="text-sm text-neutral-500">Harga acuan</p>
        <p class="text-3xl font-semibold text-neutral-800">
          Rp{{ number_format($service->default_price, 0, ',', '.') }}
        </p>
        <p class="mt-1 text-sm text-neutral-500">Harga final menyesuaikan kondisi dinamo setelah diperiksa.</p>
      </div>

      @if ($service->description)
        <p class="text-neutral-600 whitespace-pre-line">{{ $service->description }}</p>
      @endif

    </div>
  </div>

</section>
@endsection