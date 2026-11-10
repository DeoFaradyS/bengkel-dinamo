@extends('layouts.booking')

@section('title', 'Masuk - Budi Dinamo')

@section('content')
<main class="max-w-[560px] mx-auto px-6 py-16">
    <h1 class="text-4xl font-bold text-neutral-800">Masuk</h1>
    <p class="mt-3 mb-8 text-neutral-500">Masuk untuk booking lebih cepat dan lihat riwayat servis.</p>

    <div class="p-8 bg-white rounded-[10px] shadow-[0px_0px_60px_0px_rgba(0,0,0,0.08)]">
        <form method="POST" action="/masuk" class="flex flex-col gap-5">
            @csrf

            <div>
                <label for="email" class="block mb-2 text-sm font-semibold">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    autocomplete="email"
                    class="w-full px-4 py-3 border border-neutral-300 rounded-[10px] focus:outline-none focus:border-red-600">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block mb-2 text-sm font-semibold">Kata sandi</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                    class="w-full px-4 py-3 border border-neutral-300 rounded-[10px] focus:outline-none focus:border-red-600">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="size-4 accent-red-600">
                Ingat saya
            </label>

            <button type="submit"
                class="px-6 py-3 bg-neutral-800 rounded-[10px] text-white font-semibold transition-colors hover:bg-neutral-700">
                Masuk
            </button>
        </form>

        <p class="mt-6 text-sm text-neutral-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-red-600">Daftar</a>
        </p>
    </div>
</main>
@endsection