<?php

namespace App\Support;

/**
 * Nomor HP Indonesia (aturan 35). Semua bentuk penulisan dirapikan jadi 08...:
 * "+62 812-3456-7890", "62812...", "0812...", dan "812..." (tanpa 0) sama-sama jadi "0812...".
 * Spasi, strip, titik, kurung, dan tanda + dibuang. Setelah dirapikan, nomor sah kalau
 * 08 diikuti 8 sampai 12 angka (total 10-14 digit), sama di semua form.
 */
final class PhoneNumber
{
    public const REGEX = '/^08[0-9]{8,12}$/';

    public const MESSAGE = 'Nomor HP tidak valid. Contoh: 081234567890.';

    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0' . $digits;
        }

        return $digits;
    }

    public static function isValid(?string $phone): bool
    {
        return (bool) preg_match(self::REGEX, (string) self::normalize($phone));
    }

    /** Aturan validasi untuk nilai yang belum dirapikan (dirapikan dulu, lalu dicek). */
    public static function rule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (filled($value) && ! self::isValid((string) $value)) {
                $fail(self::MESSAGE);
            }
        };
    }
}
