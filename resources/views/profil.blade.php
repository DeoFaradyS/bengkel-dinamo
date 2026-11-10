@extends('layouts.customer')

@section('title', 'Profil - Budi Dinamo')

@section('content')
    @php
        $input = 'w-full rounded-[10px] border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-800 outline-none transition-colors placeholder:text-neutral-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 disabled:cursor-not-allowed disabled:bg-neutral-100 disabled:text-neutral-500';
    @endphp

    <h1 class="text-3xl font-bold text-neutral-800">Profil</h1>
    <p class="mt-2 mb-6 text-neutral-500">Kelola data akun kamu.</p>

    @if (session('status'))
        <div class="mb-6 rounded-[10px] border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('profil') }}" class="rounded-[10px] border border-neutral-200 bg-white">
        @csrf
        @method('PUT')

        <header class="border-b border-neutral-200 px-6 py-4">
            <h2 class="text-base font-semibold text-neutral-800">Informasi Akun</h2>
            <p class="mt-1 text-sm text-neutral-500">Nama dan nomor HP dipakai bengkel untuk menghubungi kamu soal servis.</p>
        </header>

        <div class="grid gap-5 p-6 sm:grid-cols-2">
            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-semibold text-neutral-800">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255"
                    autocomplete="name" class="{{ $input }}">
                @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="phone" class="text-sm font-semibold text-neutral-800">Nomor HP</label>
                <input id="phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone', $user->phone) }}"
                    autocomplete="tel" placeholder="081234567890" class="{{ $input }}">
                @error('phone') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="text-xs text-neutral-500">Wajib diisi saat booking.</p>
            </div>

            <div class="flex flex-col gap-2 sm:col-span-2">
                <label for="email" class="text-sm font-semibold text-neutral-800">Email</label>
                <div class="relative">
                    <input id="email" type="email" value="{{ $user->email }}" disabled class="{{ $input }} pr-11">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="pointer-events-none absolute right-4 top-1/2 size-4 -translate-y-1/2 text-neutral-400" aria-hidden="true">
                        <rect width="18" height="11" x="3" y="11" rx="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
                <p class="text-xs text-neutral-500">Email dipakai untuk masuk dan tidak bisa diubah.</p>
            </div>
        </div>

        <footer class="flex items-center justify-end gap-3 border-t border-neutral-200 px-6 py-4">
            <a href="{{ route('riwayat') }}" class="px-4 py-3 text-sm font-semibold text-neutral-600 transition-colors hover:text-red-600">Batal</a>
            <button type="submit"
                class="rounded-[10px] bg-neutral-800 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-neutral-700">
                Simpan
            </button>
        </footer>
    </form>
@endsection