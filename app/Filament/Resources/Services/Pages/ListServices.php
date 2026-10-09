<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
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
        $counts = Service::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $tabs = [
            'all' => Tab::make('Semua')->badge($counts->sum()),
        ];

        foreach (ServiceStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->badge($counts[$status->value] ?? 0)
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}