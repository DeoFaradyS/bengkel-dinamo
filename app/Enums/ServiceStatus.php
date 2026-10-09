<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ServiceStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Konfirmasi',
            self::Confirmed => 'Dikonfirmasi',
            self::InProgress => 'Sedang Dikerjakan',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Rejected => 'Ditolak',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Confirmed => 'info',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'gray',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Confirmed => 'heroicon-o-check-circle',
            self::InProgress => 'heroicon-o-wrench-screwdriver',
            self::Completed => 'heroicon-o-check-badge',
            self::Cancelled => 'heroicon-o-no-symbol',
            self::Rejected => 'heroicon-o-x-circle',
        };
    }

    public static function activeValues(): array
    {
        return [
            self::Pending->value,
            self::Confirmed->value,
            self::InProgress->value,
        ];
    }
}