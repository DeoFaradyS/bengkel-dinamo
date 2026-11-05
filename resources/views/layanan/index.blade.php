@extends('layouts.booking')

@section('content')
<section class="max-w-[1200px] mx-auto px-1 flex flex-col items-center gap-12 py-20">

  <div class="flex flex-col items-center gap-3 text-center">
    <h1 class="max-w-[700px] text-5xl lg:text-6xl font-semibold leading-tight">Semua Layanan</h1>
    <p class="text-neutral-500">Servis dinamo starter, alternator, dan kelistrikan mobil.</p>
  </div>

  @if ($services->isEmpty())
    <p class="max-w-[480px] text-center text-neutral-500">
      Daftar layanan belum tersedia. Tanya layanan dan harga lewat WhatsApp.
    </p>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 w-full">
      @foreach ($services as $service)
        <a href="{{ url('/layanan/'.$service->id) }}" class="block focus:outline-none focus:ring-2 focus:ring-red-500 rounded-[10px]">
          <x-service-card
            :name="$service->name"
            :description="$service->description"
            :price="$service->default_price"
            :photo-url="$service->photo_url"
          />
        </a>
      @endforeach
    </div>

    <div class="w-full">{{ $services->links() }}</div>
  @endif

</section>
@endsection