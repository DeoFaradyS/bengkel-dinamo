<?php

namespace App\Filament\Pages;

use App\Enums\BookingType;
use App\Models\WorkshopSetting;
use App\Support\GoogleMapsLink;
use App\Support\PhoneNumber;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use UnitEnum;

class JarakOngkos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
    protected static ?string $navigationLabel = 'Jarak & Ongkos';
    protected static ?string $title = 'Jarak & Ongkos';
    protected static string|UnitEnum|null $navigationGroup = 'Bengkel';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.jarak-ongkos';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = WorkshopSetting::firstOrFail();

        $this->form->fill([
            'max_distance_km' => (float) $setting->max_distance_km,
            'fuel_price' => (int) $setting->fuel_price,
            'phone' => $setting->phone,
            'maps_url' => $setting->maps_url,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 2])
                    ->extraAttributes(['class' => 'jo-grid'])
                    ->schema([
                        Section::make('Pengaturan Bengkel')
                            ->columnSpan(['default' => 1, 'lg' => 1])
                            ->columns(2)
                            ->schema([
                                TextInput::make('max_distance_km')
                                    ->label('Jarak Maksimal (km)')
                                    ->required()
                                    ->numeric()
                                    ->rule('gt:0')
                                    ->maxValue(9999),
                                TextInput::make('fuel_price')
                                    ->label('Harga BBM per liter')
                                    ->required()
                                    ->numeric()
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->minValue(1)
                                    ->rule('gt:0')
                                    ->maxValue(99999)
                                    ->prefix('Rp')
                                    ->live(onBlur: true),
                                // Nomor bengkel (aturan 25): dipakai di landing (footer, tombol WA) dan notifikasi WA.
                                TextInput::make('phone')
                                    ->label('Nomor HP / WhatsApp bengkel')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('Contoh: 081234567890')
                                    ->helperText('Tampil di halaman depan dan dipakai untuk tombol WhatsApp.')
                                    ->columnSpanFull(),
                            ]),

                        // Lokasi bengkel dari link Google Maps (aturan 25): koordinat dibaca otomatis.
                        Section::make('Lokasi Bengkel')
                            ->extraAttributes(['class' => 'jo-preview'])
                            ->columnSpan(['default' => 1, 'lg' => 1])
                            ->schema([
                                TextInput::make('maps_url')
                                    ->label('Link Google Maps')
                                    ->required()
                                    ->maxLength(500)
                                    ->placeholder('https://maps.app.goo.gl/...')
                                    ->helperText('Buka Google Maps, cari lokasi bengkel, tekan Bagikan, lalu salin link ke sini.')
                                    ->live(onBlur: true)
                                    ->rules([
                                        fn() => function (string $attribute, $value, \Closure $fail) {
                                            if (! GoogleMapsLink::coordinates($value)) {
                                                $fail('Lokasi tidak terbaca dari link ini. Pakai link dari tombol Bagikan di Google Maps.');
                                            }
                                        },
                                    ]),
                                View::make('filament.components.maps-preview')
                                    ->viewData(fn(Get $get) => [
                                        'lat' => GoogleMapsLink::coordinates($get('maps_url'))['lat'] ?? null,
                                        'lng' => GoogleMapsLink::coordinates($get('maps_url'))['lng'] ?? null,
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $coords = GoogleMapsLink::coordinates($state['maps_url']);

        WorkshopSetting::firstOrFail()->update([
            'max_distance_km' => $state['max_distance_km'],
            'fuel_price' => $state['fuel_price'],
            'phone' => PhoneNumber::normalize($state['phone']) ?? $state['phone'],
            'maps_url' => $state['maps_url'],
            'latitude' => $coords['lat'],
            'longitude' => $coords['lng'],
        ]);

        Notification::make()->title('Tersimpan')->success()->send();
    }
}