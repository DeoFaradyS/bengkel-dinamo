<?php

namespace App\Filament\Widgets;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\SpareParts\SparePartResource;
use App\Models\Service;
use App\Models\SparePart;
use App\Support\Dashboard\ServiceMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Dasbor bagian "Hari ini" (aturan 24): angka kondisi saat ini, tidak ikut filter periode.
 */
class TodayStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    public function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $today = Service::query()
            ->whereDate('scheduled_at', today())
            ->whereIn('status', [ServiceStatus::Confirmed->value, ServiceStatus::InProgress->value])
            ->count();

        $lowStock = SparePart::query()->whereColumn('stock', '<=', 'min_stock')->count();

        return [
            Stat::make('Menunggu konfirmasi', (string) ServiceMetrics::pendingNow())
                ->description('Booking yang belum dijawab')
                ->url(ServiceResource::getUrl('index', ['tab' => ServiceStatus::Pending->value])),
            Stat::make('Servis hari ini', (string) $today)
                ->description('Dikonfirmasi dan sedang dikerjakan'),
            Stat::make('Stok menipis', (string) $lowStock)
                ->description('Spare part di bawah stok minimal')
                ->color($lowStock > 0 ? 'danger' : null)
                ->url(SparePartResource::getUrl('index')),
        ];
    }
}
