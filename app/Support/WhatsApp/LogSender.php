<?php

namespace App\Support\WhatsApp;

use Illuminate\Support\Facades\Log;

// Mode lokal: pesan tidak dikirim, hanya ditulis ke log. Dipakai kalau FONNTE_TOKEN kosong.
final class LogSender implements WhatsAppSender
{
    public function send(string $phone, string $message): array
    {
        Log::info('WhatsApp (mode log) ke ' . $phone, ['message' => $message]);

        return ['ok' => true, 'detail' => 'Mode log: tidak dikirim sungguhan (FONNTE_TOKEN kosong).'];
    }

    public function name(): string
    {
        return 'log';
    }
}
