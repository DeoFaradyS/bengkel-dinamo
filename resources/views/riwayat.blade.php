@extends('layouts.booking')

@section('title', 'Riwayat Saya - Budi Dinamo')

@section('content')
<main class="max-w-[720px] mx-auto px-6 py-10 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-bold text-neutral-800">Riwayat Saya</h1>
    <p class="mt-3 mb-8 text-neutral-500">Semua booking servis dari akun ini.</p>

    @if ($services->isEmpty())
        <div class="flex flex-col gap-4 items-start p-6 bg-neutral-50 border border-neutral-200 rounded-[10px]">
            <p class="text-neutral-600">Belum ada booking.</p>
            <a href="/" class="px-6 py-3 bg-neutral-800 rounded-[10px] text-white font-semibold transition-colors hover:bg-neutral-700">
                Buat booking
            </a>
        </div>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($services as $service)
                <div class="flex flex-col gap-1 p-4 bg-neutral-50 border border-neutral-200 rounded-[10px]">
                    <p class="font-semibold text-neutral-800">{{ $service->status->getLabel() }}</p>
                    <p class="text-sm text-neutral-600">
                        {{ $service->booking_type->getLabel() }} &middot; {{ $service->scheduled_at?->format('d/m/Y, H.i') ?? '-' }}
                    </p>
                    @if ($service->complaint)
                        <p class="text-sm text-neutral-500">{{ $service->complaint }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $services->links() }}
        </div>
    @endif
</main>
@endsection