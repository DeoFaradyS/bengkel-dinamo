<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\BookingType;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ServiceInfolist
{
    private static function rupiah(mixed $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private static function showMap($record): bool
    {
        return $record->booking_type === BookingType::HomeService
            && filled($record->latitude)
            && filled($record->longitude);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'xl' => 2])
            ->components([
                Section::make('Data Pelanggan')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('customer_name')->label('Nama Pelanggan'),
                        TextEntry::make('phone')->label('No. HP'),
                    ]),

                Section::make('Detail Servis')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('scheduled_at')->label('Tanggal & Jam')->dateTime('d/m/Y H:i'),
                        TextEntry::make('status')->label('Status')->badge(),
                        TextEntry::make('cancel_reason')->label('Alasan')
                            ->visible(fn($record) => filled($record->cancel_reason)),
                        TextEntry::make('complaint')->label('Keluhan'),
                    ]),

                Section::make('Lokasi')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('booking_type')->label('Tipe Layanan')->badge(),
                        TextEntry::make('address')->label('Alamat atau patokan')
                            ->visible(fn($record) => filled($record->address)),
                        TextEntry::make('maps_link')->label('Peta')
                            ->state('Buka di Google Maps')
                            ->color('info')
                            ->url(fn($record) => "https://www.google.com/maps?q={$record->latitude},{$record->longitude}")
                            ->openUrlInNewTab()
                            ->visible(fn($record) => self::showMap($record)),
                    ]),

                Section::make('Jasa')
                    ->columnSpanFull()
                    ->visible(fn($record) => $record->items()->exists())
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(2)
                            ->schema([
                                TextEntry::make('name')->label('Jasa'),
                                TextEntry::make('price')->label('Harga')
                                    ->formatStateUsing(fn($state) => self::rupiah($state)),
                            ]),
                    ]),

                Section::make('Spare Part')
                    ->columnSpanFull()
                    ->visible(fn($record) => $record->serviceParts()->exists())
                    ->schema([
                        RepeatableEntry::make('serviceParts')
                            ->hiddenLabel()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('sparePart.name')->label('Part'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('unit_price')->label('Harga')
                                    ->formatStateUsing(fn($state) => self::rupiah($state)),
                            ]),
                    ]),

                Section::make('Ringkasan')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('labor_total')->label('Total Jasa')
                            ->formatStateUsing(fn($state) => self::rupiah($state)),
                        TextEntry::make('parts_total')->label('Total Spare Part')
                            ->formatStateUsing(fn($state) => self::rupiah($state)),
                        TextEntry::make('grand_total')->label('Total Servis')
                            ->weight(FontWeight::Bold)
                            ->formatStateUsing(fn($state) => self::rupiah($state)),
                    ]),
            ]);
    }
}