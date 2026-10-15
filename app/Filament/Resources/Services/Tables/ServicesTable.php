<?php

namespace App\Filament\Resources\Services\Tables;

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Support\WhatsAppLink;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

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
                TextColumn::make('customer_name')->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('phone')->label('No. HP')
                    ->searchable(),
                TextColumn::make('scheduled_at')->label('Tanggal & Jam')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('booking_type')->label('Tipe')
                    ->badge(),
                TextColumn::make('status')->label('Status')
                    ->badge(),
                TextColumn::make('total')->label('Total')
                    ->state(fn($record) => (float) ($record->items_sum_price ?? 0)
                        + (float) ($record->parts_sum ?? 0))
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->sortable(query: fn($query, string $direction) => $query
                        ->orderByRaw('(COALESCE(items_sum_price, 0) + COALESCE(parts_sum, 0)) ' . $direction)),
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
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn($record) => $record->status->isFinal()),
                EditAction::make()
                    ->visible(fn($record) => !$record->status->isFinal()),
                Action::make('map')
                    ->label('Buka Peta')
                    ->icon(Heroicon::OutlinedMapPin)
                    ->color('info')
                    ->visible(fn($record) => $record->latitude !== null && $record->longitude !== null)
                    ->url(fn($record) => "https://www.google.com/maps?q={$record->latitude},{$record->longitude}")
                    ->openUrlInNewTab(),
                Action::make('invoice')
                    ->label('Invoice')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->visible(fn($record) => $record->status === ServiceStatus::Completed)
                    ->action(fn($record) => response()->streamDownload(
                        fn() => print ($record->invoicePdf()),
                        $record->invoiceNumber() . '.pdf',
                    )),
                Action::make('whatsapp')
                    ->label('Chat WA')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->visible(fn($record) => filled($record->phone))
                    ->url(fn($record) => WhatsAppLink::make(
                        $record->phone,
                        match ($record->status) {
                            ServiceStatus::Pending => "Halo {$record->customer_name}, booking servis Anda sudah kami terima. Kami konfirmasi jadwalnya segera.",
                            ServiceStatus::Completed => "Halo {$record->customer_name}, servis motor Anda sudah selesai. Silakan diambil. Terima kasih.",
                            default => "Halo {$record->customer_name}, kami dari bengkel menghubungi soal servis Anda.",
                        },
                    ))
                    ->openUrlInNewTab(),
            ]);
    }
}