<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

// Siapa yang membuat servis. Pelanggan (booking online di beranda): data pelanggan
// dikunci di panel admin. Bengkel (Tambah servis di panel): bebas diubah selama aktif.
// Nilai DB tetap 'online' dan 'admin'; yang berubah hanya label (20 Nov 2026).
enum ServiceSource: string implements HasColor, HasLabel
{
    case Online = 'online';
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Online => 'Pelanggan',
            self::Admin => 'Bengkel',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Online => 'info',
            self::Admin => 'gray',
        };
    }
}
