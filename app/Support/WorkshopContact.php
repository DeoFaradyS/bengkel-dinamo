<?php

namespace App\Support;

use App\Models\WorkshopSetting;

/**
 * Nomor HP/WhatsApp bengkel (aturan 25), diatur di menu Jarak & Ongkos. Dipakai di footer
 * landing, tombol WA melayang, link konfirmasi booking, Cek Booking, dan nanti notifikasi WA.
 * Kalau belum diisi, memakai config('booking.whatsapp').
 */
final class WorkshopContact
{
    /** Nomor lokal, contoh 08882509745. */
    public static function phone(): string
    {
        return WorkshopSetting::query()->value('phone') ?: (string) config('booking.whatsapp');
    }

    /** Untuk ditampilkan, contoh +62 888-2509-745. */
    public static function display(): string
    {
        $local = PhoneNumber::normalize(self::phone()) ?? self::phone();
        $rest = str_starts_with($local, '0') ? substr($local, 1) : $local;

        return '+62 ' . implode('-', array_filter([substr($rest, 0, 3), substr($rest, 3, 4), substr($rest, 7)]));
    }

    /** Untuk link tel:, contoh +628882509745. */
    public static function tel(): string
    {
        $local = PhoneNumber::normalize(self::phone()) ?? self::phone();

        return '+62' . (str_starts_with($local, '0') ? substr($local, 1) : $local);
    }
}
