<?php

namespace App\Support\WhatsApp;

use Illuminate\Support\Facades\Http;

/**
 * Kirim lewat Fonnte (WhatsApp tidak resmi, dari nomor bengkel yang ditautkan di dashboard Fonnte).
 * API: POST https://api.fonnte.com/send, token di header Authorization (tanpa "Bearer"),
 * isian target dan message. Nomor 08... diubah Fonnte jadi 62... (countryCode bawaan '62').
 * Berhasil: {"status": true, "detail": "..."}; gagal: {"status": false, "reason": "..."}.
 */
final class FonnteSender implements WhatsAppSender
{
    public function __construct(private string $token)
    {
    }

    public function send(string $phone, string $message): array
    {
        try {
            $res = Http::asForm()
                ->withHeaders(['Authorization' => $this->token])
                ->timeout(15)
                ->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '62',
                ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'detail' => 'Tidak tersambung ke Fonnte: ' . $e->getMessage()];
        }

        // Dokumentasi kadang menulis "Status", kadang "status".
        $json = array_change_key_case((array) $res->json(), CASE_LOWER);
        $ok = $res->successful() && ($json['status'] ?? false) === true;

        return [
            'ok' => $ok,
            'detail' => (string) ($json['detail'] ?? $json['reason'] ?? ('HTTP ' . $res->status())),
        ];
    }

    public function name(): string
    {
        return 'fonnte';
    }
}
