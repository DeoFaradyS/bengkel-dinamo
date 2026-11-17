@extends('layouts.auth')

@section('title', 'Daftar - Budi Dinamo')

@section('content')
    <h1 class="text-3xl sm:text-4xl font-bold text-neutral-800">Daftar</h1>
    <p class="mt-3 mb-6 sm:mb-8 text-neutral-500">Buat akun supaya booking lebih cepat.</p>

    <div class="p-6 sm:p-8 bg-white rounded-[10px] shadow-[0px_0px_60px_0px_rgba(0,0,0,0.08)]">
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

            <div x-data="{ show: false }">
                <label for="password" class="block mb-2 text-sm font-semibold">Kata sandi</label>
                <div class="relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required
                        autocomplete="new-password" minlength="8"
                        class="w-full px-4 py-3 pr-24 border border-neutral-300 rounded-[10px] focus:outline-none focus:border-red-600">
                    <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-4 text-sm font-semibold text-neutral-500 hover:text-red-600"
                        x-text="show ? 'Sembunyi' : 'Lihat'">Lihat</button>
                </div>
                <p class="mt-2 text-sm text-neutral-500">Minimal 8 karakter.</p>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
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
@endsection