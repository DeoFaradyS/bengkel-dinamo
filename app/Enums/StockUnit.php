<?php

namespace App\Enums;

/**
 * Satuan suku cadang (aturan 33), seperti Unit of Measure di software persediaan.
 * Satuan yang ditimbang/diukur (kg, meter, liter) boleh desimal sampai 2 angka (0,35 kg);
 * satuan hitungan (pcs, set, pasang) harus bilangan bulat.
 */
enum StockUnit: string
{
    case Pcs = 'pcs';
    case Set = 'set';
    case Pasang = 'pasang';
    case Kg = 'kg';
    case Meter = 'meter';
    case Liter = 'liter';

    public function label(): string
    {
        return $this->value;
    }

    public function allowsDecimal(): bool
    {
        return in_array($this, [self::Kg, self::Meter, self::Liter], true);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn(self $u) => [$u->value => $u->label()])->all();
    }
}
