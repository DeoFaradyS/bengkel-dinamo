<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Jumlah barang (stok, qty servis, perubahan stok) disimpan decimal(10,2) (aturan 33).
 * Dibaca sebagai int kalau bulat (2 pcs) dan float kalau pecahan (0,35 kg), supaya angka bulat
 * tetap tampil dan dihitung seperti sebelum ada satuan desimal.
 */
class Quantity implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): int|float|null
    {
        if ($value === null) {
            return null;
        }

        $number = round((float) $value, 2);

        return floor($number) == $number ? (int) $number : $number;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null || $value === '' ? null : number_format(round((float) $value, 2), 2, '.', '');
    }
}
