<?php

namespace App\Filament\Pages;

use App\Models\WorkshopSetting;
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
use Filament\Forms\Components\ToggleButtons;

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
            'latitude' => $setting->latitude,
            'longitude' => $setting->longitude,
            'distance_method' => $setting->distance_method?->value ?? 'haversine',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 5])
                    ->extraAttributes(['class' => 'jo-grid'])
                    ->schema([
                        Section::make('Pengaturan Home Service')
                            ->columnSpan(['default' => 1, 'lg' => 3])
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
                                    ->live(onBlur: true)
                                    ->helperText(fn(Get $get) => 'Setara Rp'
                                        . number_format((int) str_replace('.', '', (string) $get('fuel_price')) / 40, 0, ',', '.')
                                        . ' per km (harga BBM ÷ 40 km/liter)'),
                                TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->required()
                                    ->numeric()
                                    ->minValue(-90)
                                    ->maxValue(90)
                                    ->live(onBlur: true),
                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->required()
                                    ->numeric()
                                    ->minValue(-180)
                                    ->maxValue(180)
                                    ->live(onBlur: true),
                                ToggleButtons::make('distance_method')
                                    ->label('Metode Jarak')
                                    ->options([
                                        'haversine' => 'Haversine (garis lurus)',
                                        'ors' => 'ORS (lewat jalan)',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Pratinjau')
                            ->extraAttributes(['class' => 'jo-preview'])
                            ->columnSpan(['default' => 1, 'lg' => 2])
                            ->schema([
                                View::make('filament.components.maps-preview')
                                    ->viewData(fn(Get $get) => [
                                        'lat' => $get('latitude'),
                                        'lng' => $get('longitude'),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        WorkshopSetting::firstOrFail()->update($this->form->getState());

        Notification::make()->title('Tersimpan')->success()->send();
    }
}