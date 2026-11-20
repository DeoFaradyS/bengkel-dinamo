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
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Validation\Rules\Unique;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use App\Models\Service;
use App\Support\ServiceSummaryHtml;
use App\Support\Booking\Slots;
use Illuminate\Support\Carbon;

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

    private static function statusOptions(?Service $record): array
    {
        $current = $record?->status;

        // Create: servis buatan admin langsung Dikonfirmasi, Dikerjakan, atau Selesai
        // (tidak ada yang perlu ditunggu, dan tidak masuk akal dibuat dalam keadaan batal).
        // Edit: status sekarang + yang sah.
        $cases = $current instanceof ServiceStatus
            ? [$current, ...$current->allowedNext()]
            : [ServiceStatus::Confirmed, ServiceStatus::InProgress, ServiceStatus::Completed];

        return collect($cases)
            ->mapWithKeys(fn(ServiceStatus $s) => [$s->value => $s->getLabel()])
            ->all();
    }

    // Booking online: data pelanggan hanya bisa dilihat. Lihat Service::isCustomerDataLocked().
    private static function locked(?Service $record): bool
    {
        return $record?->isCustomerDataLocked() ?? false;
    }

    // Jasa dan spare part baru diisi saat servis dikerjakan (atau langsung diselesaikan).
    private static function workStarted(Get $get): bool
    {
        $status = $get('status');
        $status = $status instanceof ServiceStatus ? $status : ServiceStatus::tryFrom((string) $status);

        return in_array($status, [ServiceStatus::InProgress, ServiceStatus::Completed], true);
    }

    // Montir sudah bisa ditentukan sejak booking dikonfirmasi (siapa yang berangkat atau memegang).
    private static function mechanicAllowed(Get $get): bool
    {
        $status = $get('status');
        $status = $status instanceof ServiceStatus ? $status : ServiceStatus::tryFrom((string) $status);

        return in_array($status, [ServiceStatus::Confirmed, ServiceStatus::InProgress, ServiceStatus::Completed], true);
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
                'name' => $r['name'] ?? ServiceType::withTrashed()->find($r['service_type_id'])?->name ?? '-',
                'total' => self::num($r['price'] ?? 0),
            ])->values()->all();
    }

    private static function partRows(Get $get): array
    {
        $rows = collect($get('serviceParts') ?? [])
            ->filter(fn($r) => filled($r['spare_part_id'] ?? null));

        $parts = SparePart::whereIn('id', $rows->pluck('spare_part_id'))->get(['id', 'name', 'unit'])->keyBy('id');

        return $rows->map(function ($r) use ($parts) {
            $qty = (float) ($r['quantity'] ?? 0);
            $price = self::num($r['unit_price'] ?? 0);
            $part = $parts[$r['spare_part_id']] ?? null;

            return [
                'name' => $part?->name ?? '-',
                'detail' => \App\Support\Qty::format($qty, $part?->unit) . ' × ' . self::rupiah($price),
                'total' => $qty * $price,
            ];
        })->values()->all();
    }

    public static function configure(Schema $schema): Schema
    {
        // Layar lebar: kiri data pelanggan, jasa, part; kanan status, montir, dan ringkasan biaya
        // yang menempel saat di-scroll. xl (1280px) 2/3 : 1/3 supaya tabel jasa dan part tidak
        // bertumpuk; 2xl (1536px) 5/8 : 3/8 supaya kolom kanan lebih lega.
        return $schema
            ->columns(['default' => 1, 'xl' => 3, '2xl' => 8])
            ->components([
                Group::make([
                Section::make('Data Servis')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        // Booking online: data pelanggan tampil sebagai teks biasa (tidak bisa diubah).
                        TextEntry::make('ro_customer_name')->label('Nama Pelanggan')
                            ->state(fn(?Service $record) => $record?->customer_name)
                            ->visible(fn(?Service $record) => self::locked($record)),
                        TextEntry::make('ro_customer_phone')->label('No. HP')
                            ->state(fn(?Service $record) => $record?->customer_phone)
                            ->visible(fn(?Service $record) => self::locked($record)),
                        TextEntry::make('ro_scheduled_at')->label('Tanggal & Jam')
                            ->state(fn(?Service $record) => $record?->scheduled_at?->translatedFormat('j M Y, H.i'))
                            ->visible(fn(?Service $record) => self::locked($record)),
                        TextEntry::make('ro_booking_type')->label('Tipe Layanan')
                            ->state(fn(?Service $record) => $record?->booking_type)
                            ->badge()
                            ->visible(fn(?Service $record) => self::locked($record)),
                        TextInput::make('customer_name')->label('Nama Pelanggan')
                            ->hidden(fn(?Service $record) => self::locked($record))
                            ->required(),
                        TextInput::make('customer_phone')->label('No. HP')
                            ->hidden(fn(?Service $record) => self::locked($record))
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->regex(\App\Support\PhoneNumber::REGEX)
                            ->placeholder('Contoh: 081234567890')
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn(Unique $rule) => $rule
                                    ->whereIn('status', ServiceStatus::activeValues())
                                    ->whereNull('deleted_at'),
                            )
                            ->validationMessages([
                                'unique' => 'Nomor ini masih punya servis yang belum selesai.',
                                'regex' => \App\Support\PhoneNumber::MESSAGE,
                            ]),

                        DateTimePicker::make('scheduled_at')->label('Tanggal & Jam')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('j M Y, H.i')
                            ->hidden(fn(?Service $record) => self::locked($record))
                            ->default(now())
                            ->live()
                            ->hint(function (Get $get, ?Service $record): ?string {
                                $state = $get('scheduled_at');

                                if (blank($state)) {
                                    return null;
                                }

                                try {
                                    $time = Carbon::parse($state)->startOfMinute();
                                } catch (\Throwable) {
                                    return null;
                                }

                                return Slots::isAvailable($time, $record?->id)
                                    ? null
                                    : 'Jam ini sudah terisi servis lain. Tetap boleh disimpan.';
                            })
                            ->hintColor('warning')
                            ->required(),

                        // Dua pilihan saja, jadi tombol (sekali klik) lebih cepat daripada dropdown.
                        ToggleButtons::make('booking_type')->label('Tipe Layanan')
                            ->hidden(fn(?Service $record) => self::locked($record))
                            ->options(BookingType::class)
                            ->inline()
                            // Sejajar dengan Tanggal & Jam; tanpa ikon supaya dua tombol muat. Terpilih berwarna biru.
                            ->colors(['workshop' => 'primary', 'home_service' => 'primary'])
                            ->icons(['workshop' => null, 'home_service' => null])
                            ->default(BookingType::Workshop)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (self::typeOf($state) !== BookingType::HomeService) {
                                    $set('latitude', null);
                                    $set('longitude', null);
                                    $set('address_note', null);
                                }
                            })
                            ->required(),

                        TextEntry::make('ro_complaint')->label('Keluhan')
                            ->columnSpanFull()
                            ->state(fn(?Service $record) => $record?->complaint)
                            ->visible(fn(?Service $record) => self::locked($record)),
                        TextEntry::make('ro_address_note')->label('Detail alamat')
                            ->columnSpanFull()
                            ->state(fn(?Service $record) => $record?->address_note)
                            ->visible(fn(?Service $record) => self::locked($record) && filled($record?->address_note)),
                        Textarea::make('complaint')->label('Keluhan')
                            ->hidden(fn(?Service $record) => self::locked($record))
                            ->columnSpanFull()
                            ->rows(3)
                            ->required(),

                        // Koordinat tidak diketik: diisi dari pin di peta (klik, geser, atau cari tempat).
                        // Tetap disimpan dan divalidasi karena dipakai menghitung jarak dan ongkos.
                        // Pesan error tampil di bawah peta (filament.forms.pin-map).
                        Hidden::make('latitude')
                            ->hidden(fn(?Service $record) => self::locked($record))
                            ->required(fn(Get $get) => self::isHome($get))
                            ->rules(['nullable', 'numeric', 'between:-90,90'])
                            ->validationMessages(['required' => 'Pilih lokasi di peta.'])
                            ->visible(fn(Get $get) => self::isHome($get))
                            ->dehydratedWhenHidden()
                            ->live(),
                        Hidden::make('longitude')
                            ->hidden(fn(?Service $record) => self::locked($record))
                            ->required(fn(Get $get) => self::isHome($get))
                            ->rules(['nullable', 'numeric', 'between:-180,180'])
                            ->validationMessages(['required' => 'Pilih lokasi di peta.'])
                            ->visible(fn(Get $get) => self::isHome($get))
                            ->dehydratedWhenHidden()
                            ->live(),
                        View::make('filament.forms.pin-map')
                            ->columnSpanFull()
                            ->visible(fn(Get $get, ?Service $record) => self::isHome($get) && !self::locked($record)),
                        TextInput::make('address_note')->label('Detail alamat (opsional)')
                            ->hidden(fn(?Service $record) => self::locked($record))
                            ->columnSpanFull()
                            ->maxLength(255)
                            ->visible(fn(Get $get) => self::isHome($get))
                            ->dehydratedWhenHidden()
                            ->placeholder('Contoh: Jl. Raya Nguling no. 12, pagar hijau, depan Indomaret'),
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
                    ->visible(fn(Get $get) => self::workStarted($get))
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
                                TableColumn::make('Harga')->width('11rem'),
                            ])
                            ->schema([
                                Select::make('service_type_id')
                                    ->label('Jasa')
                                    // Pilihan hanya jasa aktif. Jasa terarsip yang sudah terpilih di baris ini
                                    // tetap ikut, supaya servis lama masih bisa dibuka dan disimpan.
                                    ->options(fn($record) => ServiceType::withTrashed()
                                        ->where(fn($q) => $q->whereNull('deleted_at')
                                            ->orWhere('id', $record instanceof \App\Models\ServiceItem ? $record->service_type_id : null))
                                        ->orderBy('name')
                                        ->pluck('name', 'id'))
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
                                    ->maxValue(99999999)
                                    ->prefix('Rp')
                                    ->live(onBlur: true),
                            ]),
                    ]),

                Section::make('Suku Cadang')
                    ->visible(fn(Get $get) => self::workStarted($get))
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
                            ->addActionLabel('Tambah suku cadang')
                            ->live()
                            ->table([
                                TableColumn::make('Suku cadang'),
                                TableColumn::make('Qty')->width('8rem'),
                                TableColumn::make('Harga')->width('11rem'),
                            ])
                            ->schema([
                                Select::make('spare_part_id')
                                    ->label('Suku cadang')
                                    // Nama part di baris pertama, kode dan stok kecil di baris kedua (lebih mudah dibaca
                                    // daripada satu teks panjang yang terpotong). Pencarian tetap cocok dengan kode.
                                    ->options(fn() => SparePart::query()->orderBy('name')->get()
                                        ->mapWithKeys(fn($p) => [$p->id => '<div style="line-height:1.3;">'
                                            . '<div>' . e($p->name) . '</div>'
                                            . '<div style="font-size:.75rem;color:#71717a;">' . e($p->code) . ' · stok ' . e(\App\Support\Qty::format($p->stock, $p->unit)) . '</div>'
                                            . '</div>']))
                                    ->allowHtml()
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
                                    // Satuan kg/meter/liter boleh pecahan (aturan 33), pcs/set/pasang bulat.
                                    ->step(fn(Get $get) => SparePart::find($get('spare_part_id'))?->unit->allowsDecimal() ? 0.01 : 1)
                                    ->suffix(fn(Get $get) => SparePart::find($get('spare_part_id'))?->unit->label())
                                    ->rules([
                                        fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $part = SparePart::find($get('spare_part_id'));
                                            $decimal = $part?->unit->allowsDecimal() ?? false;
                                            $value = (float) $value;

                                            if ($value <= 0 || (! $decimal && $value < 1)) {
                                                $fail($decimal ? 'Harus lebih dari 0.' : 'Min 1.');
                                            } elseif (! $decimal && floor($value) != $value) {
                                                $fail('Harus bulat.');
                                            } elseif ($part !== null && $value > $part->stock) {
                                                $fail('Maks ' . \App\Support\Qty::format($part->stock) . '.');
                                            }
                                        },
                                    ])
                                    ->default(1)
                                    ->live(onBlur: true),
                                TextInput::make('unit_price')
                                    ->label('Harga')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->prefix('Rp'),
                            ]),
                    ]),

                ])->columnSpan(['default' => 1, 'xl' => 2, '2xl' => 5]),

                Group::make([
                Section::make('Status')
                    ->schema([
                        ToggleButtons::make('status')->label('Status')
                            ->options(fn(?Service $record) => self::statusOptions($record))
                            ->colors(fn() => collect(ServiceStatus::cases())
                                ->mapWithKeys(fn(ServiceStatus $s) => [$s->value => $s->getColor()])
                                ->all())
                            ->icons(fn() => collect(ServiceStatus::cases())
                                ->mapWithKeys(fn(ServiceStatus $s) => [$s->value => $s->getIcon()])
                                ->all())
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
                            ->helperText('Alasan ini terlihat oleh pelanggan di Cek Booking.')
                            // Alasan cepat untuk pelanggan yang tidak datang (jadwal tidak mengunci antrean lagi).
                            ->hintAction(
                                \Filament\Actions\Action::make('alasan_tidak_datang')
                                    ->label('Pelanggan tidak datang')
                                    ->link()
                                    ->visible(fn(Get $get) => in_array($get('status'), [ServiceStatus::Cancelled, ServiceStatus::Cancelled->value], true))
                                    ->action(fn(Set $set) => $set('cancel_reason', 'Pelanggan tidak datang')),
                            )
                            ->placeholder('Contoh: jadwal montir penuh, kerusakan di luar layanan bengkel'),
                        // Montir: catatan internal bengkel, tidak tampil ke pelanggan.
                        TextInput::make('mechanic_names')->label('Montir')
                            ->visible(fn(Get $get) => self::mechanicAllowed($get))
                            ->maxLength(255)
                            ->placeholder('Contoh: Budi, Andi'),
                    ]),

                Section::make('Ringkasan biaya')
                    ->schema([
                        TextEntry::make('ringkasan')
                            ->hiddenLabel()
                            ->state(fn(Get $get, ?Service $record) => new HtmlString(ServiceSummaryHtml::render(
                                self::jasaRows($get),
                                self::partRows($get),
                                (float) ($record?->transport_fee ?? 0),
                            )))
                            ->html(),
                    ]),
                ])->columnSpan(['default' => 1, 'xl' => 1, '2xl' => 3])
                    ->extraAttributes(['class' => 'service-summary']),
            ]);
    }
}