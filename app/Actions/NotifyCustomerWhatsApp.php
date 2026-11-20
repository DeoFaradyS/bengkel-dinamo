<?php

namespace App\Actions;

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\WhatsappMessage;
use App\Models\WorkshopSetting;
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

        // Template aturan 29: sapaan Bapak/Ibu, label tebal (*teks*), baris rapi.
        $baris = match ($service->status) {
            ServiceStatus::Confirmed => [
                'Booking Bapak/Ibu sudah *dikonfirmasi*.',
                '',
                "*Kode:* {$kode}",
                "*Jadwal:* {$jadwal}",
                '*Jenis:* ' . $service->booking_type->getLabel(),
                '',
                ...self::lokasi($service),
                'Cek status booking: ' . url('/#cek-booking'),
                'Mau ganti jadwal atau batal? Balas pesan ini saja.',
            ],
            ServiceStatus::Completed => [
                'Servis Bapak/Ibu sudah *selesai*. Terima kasih sudah servis di Budi Dinamo.',
                '',
                "*Kode:* {$kode}",
                "*Total:* {$total}",
                '*Pembayaran:* ' . ($service->paid_at ? 'Lunas' : 'Belum lunas'),
                '',
                'Invoice (PDF): ' . self::invoiceLink($service),
                'Link berlaku 30 hari.',
            ],
            ServiceStatus::Cancelled, ServiceStatus::Rejected => [
                'Mohon maaf, booking Bapak/Ibu ' . ($service->status === ServiceStatus::Rejected ? '*tidak bisa kami terima*.' : '*kami batalkan*.'),
                '',
                "*Kode:* {$kode}",
                "*Alasan:* {$alasan}",
                '',
                'Silakan booking ulang di jadwal lain: ' . url('/#booking'),
                'Ada pertanyaan? Balas pesan ini saja.',
            ],
        };

        return implode("\n", ["Halo Bapak/Ibu {$service->customer_name},", '', ...$baris, '', 'Budi Dinamo']);
    }

    /** Ke Bengkel: link Google Maps bengkel (kalau diisi). Panggil Montir: alamat pelanggan. */
    private static function lokasi(Service $service): array
    {
        if ($service->booking_type === BookingType::HomeService) {
            return filled($service->address_note) ? ["Montir akan datang ke alamat: {$service->address_note}"] : ['Montir akan datang ke lokasi yang Bapak/Ibu pilih.'];
        }

        $maps = WorkshopSetting::query()->value('maps_url');

        return filled($maps) ? ["Lokasi bengkel: {$maps}"] : [];
    }
}
