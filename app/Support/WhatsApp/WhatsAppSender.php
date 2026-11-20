<?php

namespace App\Support\WhatsApp;

// Pengirim pesan WhatsApp ke pelanggan (aturan 27). Bisa ditukar: Fonnte (sungguhan) atau log.
interface WhatsAppSender
{
    /** @return array{ok: bool, detail: string} */
    public function send(string $phone, string $message): array;

    /** Nama pengirim untuk catatan: 'fonnte' atau 'log'. */
    public function name(): string;
}
