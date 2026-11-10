<?php

use Illuminate\Support\Facades\Auth;

// Tombol WA melayang. Kode modul: WAF.
// Letakkan di tests/Feature/Landing/WhatsAppFloatTest.php.

test('WAF-001: beranda dan /layanan memuat tombol WA melayang dengan tautan wa.me', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertSee('aria-label="Chat WhatsApp"', false)
        ->assertSee('wa.me', false);
})->with(['beranda' => '/', 'layanan' => '/layanan']);

test('WAF-002: halaman masuk dan daftar tidak memuat tombol WA', function (string $url) {
    // tests/Pest.php login user acak, tamu wajib keluar dulu.
    Auth::logout();
    app('auth')->forgetGuards();

    $this->get($url)
        ->assertOk()
        ->assertDontSee('aria-label="Chat WhatsApp"', false);
})->with(['masuk' => '/masuk', 'daftar' => '/daftar']);