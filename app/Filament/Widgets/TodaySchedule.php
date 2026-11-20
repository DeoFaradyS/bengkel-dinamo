<?php

namespace App\Filament\Widgets;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Dasbor bagian "Hari ini" (aturan 24): jadwal servis hari ini, tidak ikut filter periode.
 * Menunggu, Dikonfirmasi, Dikerjakan, dan Selesai hari ini, urut jam, dengan nomor antrean.
 */
class TodaySchedule extends TableWidget
{
    protected static ?string $heading = 'Jadwal hari ini';

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => Service::query()
                ->whereDate('scheduled_at', today())
                ->whereIn('status', [
                    ServiceStatus::Pending->value,
                    ServiceStatus::Confirmed->value,
                    ServiceStatus::InProgress->value,
                    ServiceStatus::Completed->value,
                ])
                ->orderBy('scheduled_at')->orderBy('created_at')->orderBy('id'))
            ->columns([
                TextColumn::make('antrean')->label('Antrean')
                    ->state(fn(Service $record) => $record->queueInfo()['number'] ?? null)
                    ->placeholder('-')
                    ->alignCenter(),
                TextColumn::make('scheduled_at')->label('Jam')
                    ->dateTime('H.i'),
                TextColumn::make('customer_name')->label('Pelanggan'),
                TextColumn::make('booking_type')->label('Tipe')
                    ->badge()
                    ->icon(false),
                TextColumn::make('status')->label('Status')
                    ->badge(),
            ])
            ->recordUrl(fn(Service $record) => $record->status->isFinal()
                ? ServiceResource::getUrl('view', ['record' => $record])
                : ServiceResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Tidak ada jadwal hari ini')
            ->emptyStateIcon('heroicon-o-calendar')
            ->paginated(false);
    }
}
