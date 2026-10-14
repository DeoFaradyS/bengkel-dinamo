<?php

use App\Support\WhatsAppLink;

it('WAL-001: nomor 08 diubah ke format internasional', function () {
    expect(WhatsAppLink::make('081234567890'))
        ->toBe('https://wa.me/6281234567890');
});

it('WAL-002: nomor +62 dan 62 menghasilkan link sama', function (string $phone) {
    expect(WhatsAppLink::make($phone))
        ->toBe('https://wa.me/6281234567890');
})->with([
    'pakai +62' => ['+6281234567890'],
    'pakai 62' => ['6281234567890'],
    'pakai spasi dan strip' => ['0812-3456 7890'],
]);

it('WAL-003: pesan di-encode di link', function () {
    expect(WhatsAppLink::make('081234567890', 'Halo Pak Budi & tim'))
        ->toBe('https://wa.me/6281234567890?text=Halo%20Pak%20Budi%20%26%20tim');
});

it('WAL-004: pesan kosong, link tanpa parameter text', function () {
    expect(WhatsAppLink::make('081234567890', ''))
        ->toBe('https://wa.me/6281234567890');
});