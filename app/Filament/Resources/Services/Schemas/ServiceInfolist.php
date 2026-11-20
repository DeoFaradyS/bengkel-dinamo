<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn as EntryTableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use App\Support\ServiceSummaryHtml;
use Illuminate\Support\HtmlString;
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
        // Layar lebar: kiri data, jasa, dan part (2/3), kanan ringkasan biaya (1/3), sama dengan form.
        return $schema
            ->columns(['default' => 1, 'xl' => 3, '2xl' => 8])
            ->components([
                Group::make([
                Section::make('Data Servis')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextEntry::make('booking_code')->label('Kode Booking')->copyable(),
                        TextEntry::make('customer_name')->label('Nama Pelanggan'),
                        TextEntry::make('customer_phone')->label('No. HP'),
                        TextEntry::make('scheduled_at')->label('Tanggal & Jam')->dateTime('j M Y, H.i'),
                        TextEntry::make('booking_type')->label('Tipe Layanan')->badge(),
                        TextEntry::make('complaint')->label('Keluhan')
                            ->columnSpanFull(),
                        TextEntry::make('address_note')->label('Detail alamat')
                            ->columnSpanFull()
                            ->visible(fn($record) => filled($record->address_note)),
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

                ])->columnSpan(['default' => 1, 'xl' => 2, '2xl' => 5]),

                Group::make([
                Section::make('Status')
                    ->schema([
                        TextEntry::make('status')->label('Status')->badge(),
                        TextEntry::make('mechanic_names')->label('Montir')
                            ->visible(fn($record) => filled($record->mechanic_names)),
                        TextEntry::make('payment_status')->label('Pembayaran')->badge()
                            ->state(fn($record) => $record->paid_at ? 'Lunas' : 'Belum Lunas')
                            ->color(fn($state) => $state === 'Lunas' ? 'success' : 'warning')
                            ->visible(fn($record) => $record->status === ServiceStatus::Completed),
                        TextEntry::make('paid_at')->label('Dibayar pada')->dateTime('j M Y, H.i')
                            ->visible(fn($record) => $record->status === ServiceStatus::Completed && filled($record->paid_at)),
                        TextEntry::make('cancel_reason')->label('Alasan')
                            ->visible(fn($record) => filled($record->cancel_reason)),
                    ]),

                Section::make('Ringkasan biaya')
                    ->schema([
                        TextEntry::make('ringkasan')
                            ->hiddenLabel()
                            ->state(fn($record) => new HtmlString(ServiceSummaryHtml::render(
                                $record->items->map(fn($i) => ['name' => $i->name, 'total' => (float) $i->price])->all(),
                                $record->serviceParts->map(fn($p) => [
                                    'name' => $p->sparePart?->name ?? '-',
                                    'detail' => rtrim(rtrim(number_format((float) $p->quantity, 2, ',', '.'), '0'), ',') . ' × ' . ServiceSummaryHtml::rupiah((float) $p->unit_price),
                                    'total' => (float) $p->subtotal,
                                ])->all(),
                                (float) $record->transport_fee,
                            )))
                            ->html(),
                    ]),
                ])->columnSpan(['default' => 1, 'xl' => 1, '2xl' => 3])
                    ->extraAttributes(['class' => 'service-summary']),
            ]);
    }
}