<?php

namespace App\Actions;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\WhatsappMessage;
use App\Support\WhatsApp\WhatsAppSender;
use Illuminate\Support\Facades\URL;

/**
 * WhatsApp otomatis ke pelanggan saat status servis berubah (aturan 27).
 * Dikirim untuk Dikonfirmasi, Selesai (dengan total biaya), Dibatalkan, dan Ditolak (dengan alasan).
 * Pembatalan oleh pelanggan sendiri tidak dikirimi pesan. Gagal kirim tidak membatalkan status;
 * hasilnya selalu dicatat di whatsapp_messages.
 */
class NotifyCustomerWhatsApp
{
    public const EVENTS = [ServiceStatus::Confirmed, ServiceStatus::Completed, ServiceStatus::Cancelled, ServiceStatus::Rejected];

    public function __construct(private WhatsAppSender $sender)
    {
    }

    public function handle(Service $service): ?WhatsappMessage
    {
        $service = $service->fresh(['items', 'serviceParts']);

        if ($service === null || blank($service->customer_phone)) {
            return null;
        }

        $message = self::message($service);

        if ($message === null) {
            return null;
        }

        $result = $this->sender->send($service->customer_phone, $message);

        return WhatsappMessage::create([
            'service_id' => $service->id,
            'event' => $service->status,
            'phone' => $service->customer_phone,
            'message' => $message,
            'sender' => $this->sender->name(),
            'success' => $result['ok'],
            'detail' => mb_substr($result['detail'], 0, 255),
        ]);
    }

    /**
     * Link invoice bertanda tangan, berlaku 30 hari (aturan 28). Alamatnya mengikuti alamat yang
     * dipakai admin saat menyimpan (atau APP_URL di console), jadi baru bisa dibuka dari HP
     * pelanggan kalau aplikasi sudah online atau diakses lewat alamat jaringan yang sama.
     */
    public static function invoiceLink(Service $service): string
    {
        return URL::temporarySignedRoute('invoice.public', now()->addDays(30), ['service' => $service->id]);
    }

    /** Isi pesan untuk status servis sekarang, atau null kalau status ini tidak dikirimi pesan. */
    public static function message(Service $service): ?string
    {
        if (! in_array($service->status, self::EVENTS, true)) {
            return null;
        }

        // Pelanggan membatalkan sendiri lewat Cek Booking atau Riwayat: tidak perlu dikabari.
        if ($service->status === ServiceStatus::Cancelled && str_starts_with((string) $service->cancel_reason, CancelBookingByCustomer::PREFIX)) {
            return null;
        }

        $kode = substr($service->booking_code, 0, 3) . ' ' . substr($service->booking_code, 3);
        $jadwal = $service->scheduled_at?->translatedFormat('l, j F Y') . ' pukul ' . $service->scheduled_at?->format('H.i');
        $total = 'Rp ' . number_format($service->grand_total, 0, ',', '.');
        $alasan = filled($service->cancel_reason) ? $service->cancel_reason : '-';

        $isi = match ($service->status) {
            ServiceStatus::Confirmed => "booking kamu (kode {$kode}) sudah dikonfirmasi untuk {$jadwal}. Sampai jumpa.",
            ServiceStatus::Completed => "servis kamu (kode {$kode}) sudah selesai. Total biaya {$total}.\nLihat invoice: " . self::invoiceLink($service) . "\nTerima kasih sudah servis di Budi Dinamo.",
            ServiceStatus::Cancelled => "booking kamu (kode {$kode}) dibatalkan. Alasan: {$alasan}. Silakan booking ulang kalau masih perlu.",
            ServiceStatus::Rejected => "maaf, booking kamu (kode {$kode}) belum bisa kami terima. Alasan: {$alasan}. Silakan pilih jadwal lain.",
        };

        return "Halo {$service->customer_name}, {$isi}\n\n- Budi Dinamo";
    }
}
