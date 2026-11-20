<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use App\Filament\Resources\Services\Tables\ServicesTable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        // Angka di tab = jumlah baris yang tampil saat tab diklik, jadi ikut filter Tanggal.
        // Menunggu tidak difilter (supaya booking yang belum dijawab tidak terlewat), jadi angkanya semua tanggal.
        $range = ServicesTable::dateRangeFromFilter($this->tableFilters['scheduled_at']['scheduled_at'] ?? null);

        $counts = Service::query()
            ->when($range, fn(Builder $q) => $q->whereBetween('scheduled_at', $range))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Tab Semua ikut filter (semua status di rentang itu); tab Menunggu menghitung semua tanggal.
        $tabs = [
            'all' => Tab::make('Semua')->badge((int) $counts->sum()),
        ];

        $counts[ServiceStatus::Pending->value] = Service::where('status', ServiceStatus::Pending)->count();

        foreach (ServiceStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->badge((int) ($counts[$status->value] ?? 0))
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', $status));
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}