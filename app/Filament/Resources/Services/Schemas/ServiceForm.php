<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\ServiceType;
use App\Models\SparePart;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

use Illuminate\Validation\Rules\Unique;

class ServiceForm
{
    private static function typeOf(mixed $state): ?BookingType
    {
        return $state instanceof BookingType ? $state : BookingType::tryFrom((string) $state);
    }

    private static function needsReason(Get $get): bool
    {
        $status = $get('status');
        $status = $status instanceof ServiceStatus ? $status : ServiceStatus::tryFrom((string) $status);

        return in_array($status, [ServiceStatus::Cancelled, ServiceStatus::Rejected], true);
    }

    private static function isHome(Get $get): bool
    {
        return self::typeOf($get('booking_type')) === BookingType::HomeService;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'xl' => 2])
            ->components([
                Section::make('Data Pelanggan')
                    ->columns(1)
                    ->schema([
                        TextInput::make('customer_name')->label('Nama Pelanggan')
                            ->required(),
                        TextInput::make('phone')->label('No. HP')
                            ->tel()
                            ->required()
                            ->maxLength(16)
                            ->helperText('Contoh: 081234567890')
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn(Unique $rule) => $rule
                                    ->whereIn('status', ServiceStatus::activeValues())
                                    ->whereNull('deleted_at'),
                            )
                            ->validationMessages([
                                'unique' => 'Nomor ini masih punya servis yang belum selesai.',
                            ]),
                    ]),

                Section::make('Detail Servis')
                    ->columns(1)
                    ->schema([
                        DateTimePicker::make('scheduled_at')->label('Tanggal & Jam')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->default(now())
                            ->required(),
                        ToggleButtons::make('status')->label('Status')
                            ->options(ServiceStatus::class)
                            ->default(ServiceStatus::Confirmed)
                            ->live()
                            ->inline()
                            ->required(),
                        Textarea::make('cancel_reason')->label('Alasan')
                            ->rows(2)
                            ->maxLength(1000)
                            ->required(fn(Get $get) => self::needsReason($get))
                            ->visible(fn(Get $get) => self::needsReason($get))
                            ->validationMessages([
                                'required' => 'Isi alasan penolakan atau pembatalan.',
                            ])
                            ->placeholder('Contoh: pelanggan batal, kerusakan parah'),
                        Textarea::make('complaint')->label('Keluhan')
                            ->rows(3)
                            ->required(),
                    ]),

                Section::make('Lokasi')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('booking_type')->label('Tipe Layanan')
                            ->options(BookingType::class)
                            ->default(BookingType::Workshop)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (self::typeOf($state) !== BookingType::HomeService) {
                                    $set('latitude', null);
                                    $set('longitude', null);
                                    $set('address', null);
                                }
                            })
                            ->required(),
                        TextInput::make('latitude')->label('Latitude')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->required(fn(Get $get) => self::isHome($get))
                            ->visible(fn(Get $get) => self::isHome($get))
                            ->dehydratedWhenHidden()
                            ->live(onBlur: true)
                            ->helperText('Contoh: -7.7174465'),
                        TextInput::make('longitude')->label('Longitude')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->required(fn(Get $get) => self::isHome($get))
                            ->visible(fn(Get $get) => self::isHome($get))
                            ->dehydratedWhenHidden()
                            ->live(onBlur: true)
                            ->helperText('Contoh: 113.0755426'),
                        View::make('filament.forms.pin-map')
                            ->columnSpanFull()
                            ->visible(fn(Get $get) => self::isHome($get)),
                        TextInput::make('address')->label('Alamat atau patokan (opsional)')
                            ->columnSpanFull()
                            ->maxLength(255)
                            ->visible(fn(Get $get) => self::isHome($get))
                            ->dehydratedWhenHidden()
                            ->placeholder('Contoh: depan Indomaret, pagar hijau'),
                        TextEntry::make('maps_link')->label('Peta')
                            ->columnSpanFull()
                            ->state('Buka di Google Maps')
                            ->color('info')
                            ->url(fn(Get $get) => "https://www.google.com/maps?q={$get('latitude')},{$get('longitude')}")
                            ->openUrlInNewTab()
                            ->visible(fn(Get $get) => self::isHome($get)
                                && filled($get('latitude'))
                                && filled($get('longitude'))),
                    ]),

                Section::make('Jasa')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->minItems(function (Get $get) {
                                $status = $get('status');
                                $status = $status instanceof ServiceStatus ? $status : ServiceStatus::tryFrom((string) $status);

                                return $status === ServiceStatus::Completed ? 1 : 0;
                            })
                            ->validationMessages([
                                'min' => 'Servis selesai harus punya minimal satu jasa.',
                            ])
                            ->hiddenLabel()
                            ->addActionLabel('Tambah jasa')
                            ->live()
                            ->table([
                                TableColumn::make('Jasa'),
                                TableColumn::make('Harga')->width('16rem'),
                            ])
                            ->schema([
                                Select::make('service_type_id')
                                    ->label('Jasa')
                                    ->options(fn() => ServiceType::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $type = ServiceType::find($state);
                                        $set('name', $type?->name);
                                        $set('price', (int) ($type?->default_price ?? 0));
                                    }),
                                Hidden::make('name'),
                                TextInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->numeric()
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true),
                            ]),

                        TextEntry::make('total_jasa')
                            ->label('Total Jasa')
                            ->state(function (Get $get) {
                                $total = collect($get('items') ?? [])
                                    ->sum(fn($item) => (float) str_replace('.', '', (string) ($item['price'] ?? 0)));

                                return 'Rp ' . number_format($total, 0, ',', '.');
                            }),
                    ]),

                Section::make('Spare Part')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('serviceParts')
                            ->relationship()
                            ->hiddenLabel()
                            ->addActionLabel('Tambah spare part')
                            ->live()
                            ->table([
                                TableColumn::make('Part'),
                                TableColumn::make('Qty')->width('8rem'),
                                TableColumn::make('Harga')->width('16rem'),
                            ])
                            ->schema([
                                Select::make('spare_part_id')
                                    ->label('Part')
                                    ->options(fn() => SparePart::query()->orderBy('name')->get()
                                        ->mapWithKeys(fn($p) => [$p->id => "{$p->code} - {$p->name} (stok {$p->stock})"]))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('unit_price', (int) (SparePart::find($state)?->price ?? 0));
                                    }),
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(fn(Get $get) => SparePart::find($get('spare_part_id'))?->stock)
                                    ->default(1)
                                    ->live(onBlur: true),
                                TextInput::make('unit_price')
                                    ->label('Harga')
                                    ->required()
                                    ->numeric()
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true),
                            ]),

                        TextEntry::make('total_part')
                            ->label('Total Spare Part')
                            ->state(function (Get $get) {
                                $total = collect($get('serviceParts') ?? [])->sum(
                                    fn($p) => (float) ($p['quantity'] ?? 0)
                                        * (float) str_replace('.', '', (string) ($p['unit_price'] ?? 0))
                                );

                                return 'Rp ' . number_format($total, 0, ',', '.');
                            }),
                    ]),
            ]);
    }
}