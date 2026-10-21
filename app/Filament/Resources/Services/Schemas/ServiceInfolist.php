<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\BookingType;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn as EntryTableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class ServiceInfolist
{
    private static function isHome($record): bool
    {
        return $record->booking_type === BookingType::HomeService;
    }

    private static function showMap($record): bool
    {
        return self::isHome($record)
            && filled($record->latitude)
            && filled($record->longitude);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Servis')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextEntry::make('customer_name')->label('Nama Pelanggan'),
                        TextEntry::make('phone')->label('No. HP'),
                        TextEntry::make('scheduled_at')->label('Tanggal & Jam')->dateTime('d/m/Y H:i'),
                        TextEntry::make('booking_type')->label('Tipe Layanan')->badge(),
                        TextEntry::make('status')->label('Status')->badge()
                            ->columnSpanFull(),
                        TextEntry::make('cancel_reason')->label('Alasan')
                            ->columnSpanFull()
                            ->visible(fn($record) => filled($record->cancel_reason)),
                        TextEntry::make('complaint')->label('Keluhan')
                            ->columnSpanFull(),
                        TextEntry::make('address')->label('Alamat atau patokan')
                            ->columnSpanFull()
                            ->visible(fn($record) => filled($record->address)),
                        TextEntry::make('maps_link')->label('Peta')
                            ->columnSpanFull()
                            ->state('Buka di Google Maps')
                            ->color('info')
                            ->url(fn($record) => "https://www.google.com/maps?q={$record->latitude},{$record->longitude}")
                            ->openUrlInNewTab()
                            ->visible(fn($record) => self::showMap($record)),
                    ]),

                Section::make('Jasa')
                    ->visible(fn($record) => $record->items()->exists())
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->table([
                                EntryTableColumn::make('Jasa'),
                                EntryTableColumn::make('Harga')->width('12rem')->alignment(Alignment::End),
                            ])
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('price')
                                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                                    ->alignment(Alignment::End),
                            ]),
                    ]),

                Section::make('Spare Part')
                    ->visible(fn($record) => $record->serviceParts()->exists())
                    ->schema([
                        RepeatableEntry::make('serviceParts')
                            ->hiddenLabel()
                            ->table([
                                EntryTableColumn::make('Part'),
                                EntryTableColumn::make('Qty')->width('6rem')->alignment(Alignment::End),
                                EntryTableColumn::make('Harga satuan')->width('12rem')->alignment(Alignment::End),
                                EntryTableColumn::make('Jumlah')->width('12rem')->alignment(Alignment::End),
                            ])
                            ->schema([
                                TextEntry::make('sparePart.name'),
                                TextEntry::make('quantity')
                                    ->numeric()
                                    ->alignment(Alignment::End),
                                TextEntry::make('unit_price')
                                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                                    ->alignment(Alignment::End),
                                TextEntry::make('subtotal')
                                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                                    ->alignment(Alignment::End),
                            ]),
                    ]),

                Section::make('Ringkasan biaya')
                    ->schema([
                        Flex::make([
                            Text::make('Subtotal jasa')->color('gray')->grow(),
                            TextEntry::make('labor_total')
                                ->hiddenLabel()
                                ->money('IDR', locale: 'id', decimalPlaces: 0)
                                ->weight(FontWeight::SemiBold)
                                ->alignment(Alignment::End)
                                ->grow(false),
                        ]),
                        Flex::make([
                            Text::make('Subtotal spare part')->color('gray')->grow(),
                            TextEntry::make('parts_total')
                                ->hiddenLabel()
                                ->money('IDR', locale: 'id', decimalPlaces: 0)
                                ->weight(FontWeight::SemiBold)
                                ->alignment(Alignment::End)
                                ->grow(false),
                        ]),
                        Flex::make([
                            Text::make('Transport (BBM)')->color('gray')->grow(),
                            TextEntry::make('transport_fee')
                                ->hiddenLabel()
                                ->money('IDR', locale: 'id', decimalPlaces: 0)
                                ->weight(FontWeight::SemiBold)
                                ->alignment(Alignment::End)
                                ->grow(false),
                        ])->visible(fn($record) => (float) $record->transport_fee > 0),

                        Flex::make([
                            Text::make('Total Servis')->weight(FontWeight::Bold)->grow(),
                            TextEntry::make('grand_total')
                                ->hiddenLabel()
                                ->money('IDR', locale: 'id', decimalPlaces: 0)
                                ->size(TextSize::Large)
                                ->weight(FontWeight::Bold)
                                ->alignment(Alignment::End)
                                ->grow(false),
                        ])->extraAttributes([
                            'style' => 'margin-top:1rem;padding:.875rem 1rem;border-radius:.5rem;background:rgba(128,128,128,.06);',
                        ]),
                    ]),
            ]);
    }
}