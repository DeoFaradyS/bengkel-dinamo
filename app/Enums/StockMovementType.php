<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Opening = 'opening';
    case Purchase = 'purchase';
    case Usage = 'usage';
    case Correction = 'correction';

    public function label(): string
    {
        return match ($this) {
            self::Opening => 'Stok awal',
            self::Purchase => 'Barang masuk',
            self::Usage => 'Dipakai servis',
            self::Correction => 'Koreksi',
        };
    }
}