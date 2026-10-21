<?php

namespace App\Enums;

enum StockReason: string
{
    case Supplier = 'supplier';
    case Retur = 'retur';
    case OtherIn = 'other_in';
    case Damaged = 'damaged';
    case Lost = 'lost';
    case Miscount = 'miscount';
    case OtherOut = 'other_out';

    public function label(): string
    {
        return match ($this) {
            self::Supplier => 'Beli dari supplier',
            self::Retur => 'Retur',
            self::OtherIn => 'Lainnya (tambah)',
            self::Damaged => 'Rusak',
            self::Lost => 'Hilang',
            self::Miscount => 'Salah hitung',
            self::OtherOut => 'Lainnya (kurang)',
        };
    }

    public function isOut(): bool
    {
        return in_array($this, [self::Damaged, self::Lost, self::Miscount, self::OtherOut], true);
    }

    public function requiresNote(): bool
    {
        return in_array($this, [self::OtherIn, self::OtherOut], true);
    }

    // Jenis di riwayat: barang masuk normal = Purchase, sisanya = Correction.
    public function type(): StockMovementType
    {
        return match ($this) {
            self::Supplier, self::Retur => StockMovementType::Purchase,
            default => StockMovementType::Correction,
        };
    }

    public static function options(string $direction): array
    {
        $out = $direction === 'out';

        return collect(self::cases())
            ->filter(fn(self $r) => $r->isOut() === $out)
            ->mapWithKeys(fn(self $r) => [$r->value => $r->label()])
            ->all();
    }
}