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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Validation\Rules\Unique;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn as EntryTableColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

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

    private static function num(mixed $value): float
    {
        return (float) str_replace('.', '', (string) ($value ?? 0));
    }

    private static function rupiah(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    private static function jasaRows(Get $get): array
    {
        return collect($get('items') ?? [])
            ->filter(fn($r) => filled($r['service_type_id'] ?? null))
            ->map(fn($r) => [
                'name' => $r['name'] ?? ServiceType::find($r['service_type_id'])?->name ?? '-',
                'total' => self::num($r['price'] ?? 0),
            ])->values()->all();
    }

    private static function partRows(Get $get): array
    {
        $rows = collect($get('serviceParts') ?? [])
            ->filter(fn($r) => filled($r['spare_part_id'] ?? null));

        $names = SparePart::whereIn('id', $rows->pluck('spare_part_id'))->pluck('name', 'id');

        return $rows->map(function ($r) use ($names) {
            $qty = (float) ($r['quantity'] ?? 0);
            $price = self::num($r['unit_price'] ?? 0);

            return [
                'name' => $names[$r['spare_part_id']] ?? '-',
                'detail' => rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') . ' × ' . self::rupiah($price),
                'total' => $qty * $price,
            ];
        })->values()->all();
    }

    private static function withSubtotal(array $rows, string $label): array
    {
        if ($rows === []) {
            return [];
        }

        return [
            ...$rows,
            [
                'name' => $label,
                'detail' => '',
                'total' => array_sum(array_column($rows, 'total')),
                'is_subtotal' => true,
            ],
        ];
    }


    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Servis')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('customer_name')->label('Nama Pelanggan')
                            ->required(),
                        TextInput::make('phone')->label('No. HP')
                            ->tel()
                            ->required()
                            ->maxLength(16)
                            ->placeholder('Contoh: 081234567890')
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn(Unique $rule) => $rule
                                    ->whereIn('status', ServiceStatus::activeValues())
                                    ->whereNull('deleted_at'),
                            )
                            ->validationMessages([
                                'unique' => 'Nomor ini masih punya servis yang belum selesai.',
                            ]),

                        DateTimePicker::make('scheduled_at')->label('Tanggal & Jam')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->default(now())
                            ->required(),
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

                        ToggleButtons::make('status')->label('Status')
                            ->columnSpanFull()
                            ->options(ServiceStatus::class)
                            ->default(ServiceStatus::Confirmed)
                            ->live()
                            ->inline()
                            ->required(),
                        Textarea::make('cancel_reason')->label('Alasan')
                            ->columnSpanFull()
                            ->rows(2)
                            ->maxLength(1000)
                            ->required(fn(Get $get) => self::needsReason($get))
                            ->visible(fn(Get $get) => self::needsReason($get))
                            ->validationMessages([
                                'required' => 'Isi alasan penolakan atau pembatalan.',
                            ])
                            ->placeholder('Contoh: pelanggan batal, kerusakan parah'),
                        Textarea::make('complaint')->label('Keluhan')
                            ->columnSpanFull()
                            ->rows(3)
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
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                $data['price'] = (int) round((float) $data['price']);

                                return $data;
                            })
                            ->minItems(function (Get $get) {
                                $status = $get('status');
                                $status = $status instanceof ServiceStatus ? $status : ServiceStatus::tryFrom((string) $status);

                                return $status === ServiceStatus::Completed ? 1 : 0;
                            })
                            ->validationMessages([
                                'min' => 'Servis selesai harus punya minimal satu jasa.',
                            ])
                            ->hiddenLabel()
                            ->defaultItems(1)
                            ->afterStateHydrated(function (Repeater $component, $state) {
                                if (blank($state)) {
                                    $component->state([(string) Str::uuid() => []]);
                                }
                            })
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
                                    ->required(fn(Get $get) => in_array($get('../../status'), [ServiceStatus::Completed, ServiceStatus::Completed->value], true))
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $type = ServiceType::find($state);
                                        $set('name', $type?->name);
                                        $set('price', (int) ($type?->default_price ?? 0));
                                    }),
                                Hidden::make('name'),
                                TextInput::make('price')

                                    ->label('Harga')
                                    ->required(fn(Get $get) => filled($get('service_type_id')))
                                    ->numeric()
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true),
                            ]),
                    ]),

                Section::make('Spare Part')
                    ->schema([
                        Repeater::make('serviceParts')
                            ->relationship()
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                $data['unit_price'] = (int) round((float) $data['unit_price']);

                                return $data;
                            })
                            ->hiddenLabel()
                            ->defaultItems(1)
                            ->afterStateHydrated(function (Repeater $component, $state) {
                                if (blank($state)) {
                                    $component->state([(string) Str::uuid() => ['quantity' => 1]]);
                                }
                            })
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
                                    ->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('unit_price', (int) (SparePart::find($state)?->price ?? 0));
                                    }),
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->required(fn(Get $get) => filled($get('spare_part_id')))
                                    ->numeric()
                                    ->rules([
                                        fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $stock = SparePart::find($get('spare_part_id'))?->stock;

                                            if ($value < 1) {
                                                $fail('Min 1.');
                                            } elseif ($stock !== null && $value > $stock) {
                                                $fail("Maks {$stock}.");
                                            }
                                        },
                                    ])
                                    ->default(1)
                                    ->live(onBlur: true),
                                TextInput::make('unit_price')
                                    ->label('Harga')
                                    ->required(fn(Get $get) => filled($get('spare_part_id')))
                                    ->numeric()
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true),
                            ]),
                    ]),

                Section::make('Ringkasan biaya')
                    ->schema([
                        Text::make(new HtmlString(
                            '<style>.ringkasan-tabel tr:last-child > *{background:rgba(128,128,128,.06)!important;font-weight:600!important;}</style>'
                        ))->extraAttributes(['style' => 'display:none;']),

                        // ---- Jasa ----
                        RepeatableEntry::make('ringkasan_jasa')
                            ->hiddenLabel()
                            ->extraAttributes(['class' => 'ringkasan-tabel'])
                            ->state(fn(Get $get) => self::withSubtotal(self::jasaRows($get), 'Subtotal jasa'))
                            ->visible(fn(Get $get) => count(self::jasaRows($get)) > 0)
                            ->table([
                                EntryTableColumn::make('Jasa'),
                                EntryTableColumn::make('Harga')->width('12rem')->alignment(Alignment::End),
                            ])
                            ->schema([
                                TextEntry::make('name')
                                    ->weight(fn(Get $get) => $get('is_subtotal') ? FontWeight::SemiBold : null)
                                    ->color(fn(Get $get) => $get('is_subtotal') ? 'gray' : null),
                                TextEntry::make('total')
                                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                                    ->alignment(Alignment::End)
                                    ->weight(fn(Get $get) => $get('is_subtotal') ? FontWeight::SemiBold : null),
                            ]),
                        Text::make('Belum ada jasa')
                            ->color('gray')
                            ->visible(fn(Get $get) => count(self::jasaRows($get)) === 0),

                        // ---- Spare part ----
                        RepeatableEntry::make('ringkasan_part')
                            ->hiddenLabel()
                            ->extraAttributes(['class' => 'ringkasan-tabel', 'style' => 'margin-top:1rem;'])
                            ->state(fn(Get $get) => self::withSubtotal(self::partRows($get), 'Subtotal spare part'))
                            ->visible(fn(Get $get) => count(self::partRows($get)) > 0)
                            ->table([
                                EntryTableColumn::make('Spare part'),
                                EntryTableColumn::make('Qty × harga')->width('14rem')->alignment(Alignment::End),
                                EntryTableColumn::make('Jumlah')->width('12rem')->alignment(Alignment::End),
                            ])
                            ->schema([
                                TextEntry::make('name')
                                    ->weight(fn(Get $get) => $get('is_subtotal') ? FontWeight::SemiBold : null)
                                    ->color(fn(Get $get) => $get('is_subtotal') ? 'gray' : null),
                                TextEntry::make('detail')
                                    ->color('gray')
                                    ->alignment(Alignment::End),
                                TextEntry::make('total')
                                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                                    ->alignment(Alignment::End)
                                    ->weight(fn(Get $get) => $get('is_subtotal') ? FontWeight::SemiBold : null),
                            ]),
                        Text::make('Belum ada spare part')
                            ->color('gray')
                            ->visible(fn(Get $get) => count(self::partRows($get)) === 0),
                        Flex::make([
                            Text::make('Transport (BBM)')->grow(),
                            TextEntry::make('ringkasan_transport')
                                ->hiddenLabel()
                                ->state(fn($record) => (float) ($record?->transport_fee ?? 0))
                                ->money('IDR', locale: 'id', decimalPlaces: 0)
                                ->weight(FontWeight::SemiBold)
                                ->alignment(Alignment::End)
                                ->grow(false),
                        ])
                            ->extraAttributes(['style' => 'margin-top:1rem;'])
                            ->visible(fn($record) => (float) ($record?->transport_fee ?? 0) > 0),

                        // ---- Total ----
                        Flex::make([
                            Text::make('Total Servis')->weight(FontWeight::Bold)->grow(),
                            TextEntry::make('grand_total')
                                ->hiddenLabel()
                                ->state(fn(Get $get, $record) => array_sum(array_column(self::jasaRows($get), 'total'))
                                    + array_sum(array_column(self::partRows($get), 'total'))
                                    + (float) ($record?->transport_fee ?? 0))
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