<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BookingType: string implements HasColor, HasIcon, HasLabel
{
    case Workshop = 'workshop';
    case HomeService = 'home_service';

    public function getLabel(): string
    {
        return match ($this) {
            self::Workshop => 'Antar ke bengkel',
            self::HomeService => 'Montir datang ke lokasi',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Workshop => 'gray',
            self::HomeService => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Workshop => 'heroicon-o-building-storefront',
            self::HomeService => 'heroicon-o-map-pin',
        };
    }
}