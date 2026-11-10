@extends('layouts.booking')

@section('title', 'Daftar - Budi Dinamo')

@section('content')
<main class="max-w-[560px] mx-auto px-6 py-16">
    <h1 class="text-4xl font-bold text-neutral-800">Daftar</h1>
    <p class="mt-3 mb-8 text-neutral-500">Buat akun supaya data booking terisi otomatis.</p>

    <div class="p-8 bg-white rounded-[10px] shadow-[0px_0px_60px_0px_rgba(0,0,0,0.08)]">
        <form method="POST" action="/daftar" class="flex flex-col gap-5">
            @csrf

            <div>
                <label for="name" class="block mb-2 text-sm font-semibold">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                    autocomplete="name"
                    class="w-full px-4 py-3 border border-neutral-300 rounded-[10px] focus:outline-none focus:border-red-600">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block mb-2 text-sm font-semibold">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    autocomplete="email"
                    class="w-full px-4 py-3 border border-neutral-300 rounded-[10px] focus:outline-none focus:border-red-600">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block mb-2 text-sm font-semibold">Nomor HP</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                    autocomplete="tel" placeholder="08xxxxxxxxxx"
                    class="w-full px-4 py-3 border border-neutral-300 rounded-[10px] focus:outline-none focus:border-red-600">
                @error('phone')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block mb-2 text-sm font-semibold">Kata sandi</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                    class="w-full px-4 py-3 border border-neutral-300 rounded-[10px] focus:outline-none focus:border-red-600">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block mb-2 text-sm font-semibold">Ulangi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    autocomplete="new-password"
                    class="w-full px-4 py-3 border border-neutral-300 rounded-[10px] focus:outline-none focus:border-red-600">
            </div>

            <button type="submit"
                class="px-6 py-3 bg-neutral-800 rounded-[10px] text-white font-semibold transition-colors hover:bg-neutral-700">
                Daftar
            </button>
        </form>

        <p class="mt-6 text-sm text-neutral-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-red-600">Masuk</a>
        </p>
    </div>
</main>
@endsection