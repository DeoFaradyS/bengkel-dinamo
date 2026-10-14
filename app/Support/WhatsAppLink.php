<?php

namespace App\Support;

final class WhatsAppLink
{
    public static function make(string $phone, string $message = ''): string
    {
        $local = PhoneNumber::normalize($phone) ?? '';
        $international = str_starts_with($local, '0') ? '62'.substr($local, 1) : $local;

        $url = 'https://wa.me/'.$international;

        if ($message !== '') {
            $url .= '?text='.rawurlencode($message);
        }

        return $url;
    }
}