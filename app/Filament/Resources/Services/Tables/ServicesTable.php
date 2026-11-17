<?php

namespace App\Filament\Resources\Services\Tables;

use App\Actions\MarkServicePaid;
use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columnManager(false)
            ->modifyQueryUsing(fn($query) => $query
                ->withSum('items', 'price')
                ->withSum('serviceParts as parts_sum', DB::raw('quantity * unit_price')))
            ->defaultSort(fn($query) => $query
                ->orderBy('scheduled_at', 'asc')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc'))
            ->columns([
                TextColumn::make('index')->label('No')->rowIndex(),
                TextColumn::make('booking_code')->label('Kode')
                    ->searchable(),
                TextColumn::make('customer_name')->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('customer_phone')->label('No. HP')
                    ->searchable(),
                TextColumn::make('scheduled_at')->label('Tanggal & Jam')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('booking_type')->label('Tipe')
                    ->badge()
                    ->icon(false),
                TextColumn::make('source')->label('Asal')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->visible(fn($livewire) => ($livewire->activeTab ?? null) === 'all'),
                TextColumn::make('paid_at')->label('Pembayaran')
                    ->badge()
                    ->state(fn($record) => $record->status !== ServiceStatus::Completed
                        ? null
                        : ($record->paid_at ? 'Lunas' : 'Belum Lunas'))
                    ->color(fn($state) => $state === 'Lunas' ? 'success' : 'warning')
                    ->placeholder('-')
                    // Pembayaran hanya relevan untuk servis Selesai, jadi kolom hanya ada di tab Selesai.
                    ->visible(fn($livewire) => ($livewire->activeTab ?? null) === ServiceStatus::Completed->value),

                // Total = jasa + part + ongkos transport (aturan 7), sama dengan detail dan /riwayat.
                // Belum ada harga sama sekali tampil "-", bukan Rp 0.
                TextColumn::make('total')->label('Total')
                    ->visible(fn($livewire) => in_array($livewire->activeTab ?? 'all', [
                        'all',
                        ServiceStatus::Confirmed->value,
                        ServiceStatus::InProgress->value,
                        ServiceStatus::Completed->value,
                    ], true))
                    ->state(fn($record) => ((float) ($record->items_sum_price ?? 0)
                        + (float) ($record->parts_sum ?? 0)
                        + (float) ($record->transport_fee ?? 0)) ?: null)
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->placeholder('-')
                    ->sortable(query: fn($query, string $direction) => $query
                        ->orderByRaw('(COALESCE(items_sum_price, 0) + COALESCE(parts_sum, 0) + COALESCE(transport_fee, 0)) ' . $direction)),
                TextColumn::make('created_at')->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('booking_type')
                    ->label('Tipe')
                    ->options(BookingType::class),
                SelectFilter::make('pembayaran')
                    ->label('Pembayaran')
                    ->options(['lunas' => 'Lunas', 'belum_lunas' => 'Belum Lunas'])
                    ->query(fn($query, array $data) => match ($data['value'] ?? null) {
                        'lunas' => $query->where('status', ServiceStatus::Completed->value)->whereNotNull('paid_at'),
                        'belum_lunas' => $query->where('status', ServiceStatus::Completed->value)->whereNull('paid_at'),
                        default => $query,
                    })
                    ->visible(fn($livewire) => ($livewire->activeTab ?? null) === ServiceStatus::Completed->value),
            ])
            ->recordUrl(fn($record) => $record->status->isFinal()
                ? ServiceResource::getUrl('view', ['record' => $record])
                : ServiceResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                EditAction::make()
                    ->visible(fn($record) => !$record->status->isFinal()),
                ViewAction::make()
                    ->visible(fn($record) => $record->status->isFinal()),
                Action::make('tandai_lunas')
                    ->label('Tandai Lunas')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai servis ini lunas?')
                    ->modalDescription('Pembayaran tunai sudah diterima. Waktu lunas tercatat sekarang.')
                    ->visible(fn($record) => $record->status === ServiceStatus::Completed && $record->paid_at === null)
                    ->action(function ($record) {
                        $ok = app(MarkServicePaid::class)->handle($record);

                        Notification::make()
                            ->title($ok ? 'Servis ditandai lunas' : 'Servis ini sudah lunas atau belum selesai')
                            ->{$ok ? 'success' : 'warning'}()
                            ->send();
                    }),
                Action::make('invoice')
                    ->label('Invoice')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->visible(fn($record) => $record->status === ServiceStatus::Completed)
                    ->action(fn($record) => response()->streamDownload(
                        fn() => print ($record->invoicePdf()),
                        $record->invoiceNumber() . '.pdf',
                    )),
            ]);
    }
}