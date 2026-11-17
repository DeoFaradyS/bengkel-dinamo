<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

// Asal servis. Booking online: data pelanggan dikunci di panel admin.
enum ServiceSource: string implements HasColor, HasLabel
{
    case Online = 'online';
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Online => 'Booking Online',
            self::Admin => 'Dibuat Admin',
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
