<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Membaca koordinat dari link Google Maps (aturan 25), supaya pemilik bengkel cukup menempel link
 * dari tombol "Bagikan" di Google Maps, tanpa mengetik latitude dan longitude.
 *
 * Format yang dikenali:
 * - .../maps?q=-7.71,113.07  atau  ...?query=-7.71,113.07  atau  ...?ll=-7.71,113.07
 * - .../@-7.71,113.07,17z
 * - ...!3d-7.71!4d113.07 (link tempat)
 * - teks koordinat langsung: "-7.71, 113.07"
 * - link pendek maps.app.goo.gl / goo.gl/maps: diikuti dulu ke link panjangnya.
 */
final class GoogleMapsLink
{
    /** @return array{lat: float, lng: float}|null */
    public static function coordinates(?string $input): ?array
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if ($found = self::parse($input)) {
            return $found;
        }

        $host = strtolower((string) parse_url($input, PHP_URL_HOST));

        if (! in_array($host, ['maps.app.goo.gl', 'goo.gl'], true)) {
            return null;
        }

        // Link pendek perlu diikuti ke internet; hasilnya diingat dalam satu permintaan.
        if (! array_key_exists($input, self::$resolved)) {
            $long = self::resolve($input);
            self::$resolved[$input] = $long ? self::parse($long) : null;
        }

        return self::$resolved[$input];
    }

    /** @var array<string, array{lat: float, lng: float}|null> */
    private static array $resolved = [];

    /** @return array{lat: float, lng: float}|null */
    private static function parse(string $text): ?array
    {
        $text = urldecode($text);
        $num = '(-?\d{1,3}(?:\.\d+)?)';

        $patterns = [
            "/!3d{$num}!4d{$num}/",                                  // link tempat: paling tepat
            "/[?&](?:q|query|ll|center|destination)={$num},\s*{$num}/",
            "/@{$num},{$num}/",
            "/^\s*{$num}\s*,\s*{$num}\s*$/",                          // koordinat langsung
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];

                if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                    return ['lat' => round($lat, 7), 'lng' => round($lng, 7)];
                }
            }
        }

        return null;
    }

    /** Ikuti redirect link pendek sampai dapat link panjang (maksimal 5 lompatan). */
    private static function resolve(string $url): ?string
    {
        try {
            for ($i = 0; $i < 5; $i++) {
                $res = Http::withOptions(['allow_redirects' => false])->timeout(8)->get($url);
                $next = $res->header('Location');

                if (! $next) {
                    return $url;
                }

                $url = $next;

                if (self::parse($url)) {
                    return $url;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return $url;
    }
}
