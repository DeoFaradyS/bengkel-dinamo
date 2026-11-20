<?php

namespace App\Support;

use App\Enums\StockUnit;

/** Format jumlah barang gaya Indonesia (aturan 33): 2 pcs, 0,35 kg, 1.250 pcs. */
final class Qty
{
    /** Angka jumlah: int kalau bulat, float 2 desimal kalau pecahan (sama dengan cast Quantity). */
    public static function number(int|float|string|null $number): int|float
    {
        $number = round((float) $number, 2);

        return floor($number) == $number ? (int) $number : $number;
    }

    public static function format(int|float|string|null $number, StockUnit|string|null $unit = null): string
    {
        $number = round((float) $number, 2);
        $text = number_format($number, 2, ',', '.');
        $text = rtrim(rtrim($text, '0'), ',');

        $unit = $unit instanceof StockUnit ? $unit->label() : $unit;

        return filled($unit) ? $text . ' ' . $unit : $text;
    }
}
