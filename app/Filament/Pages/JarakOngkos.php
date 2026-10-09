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
            'price_per_km' => (int) $setting->price_per_km,
            'latitude' => $setting->latitude,
            'longitude' => $setting->longitude,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 2])->schema([
                    Section::make('Ongkos Transport')
                        ->schema([
                            TextInput::make('max_distance_km')
                                ->label('Jarak Maksimal (km)')
                                ->required()
                                ->numeric()
                                ->rule('gt:0')
                                ->maxValue(9999),
                            TextInput::make('price_per_km')
                                ->label('Ongkos per km')
                                ->required()
                                ->numeric()
                                ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                ->stripCharacters('.')
                                ->minValue(0)
                                ->maxValue(9999999999)
                                ->prefix('Rp'),
                        ]),

                    Section::make('Lokasi Bengkel')
                        ->schema([
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
                        ]),
                ]),

                Section::make('Pratinjau Lokasi')
                    ->schema([
                        View::make('filament.components.maps-preview')
                            ->viewData(fn(Get $get) => [
                                'lat' => $get('latitude'),
                                'lng' => $get('longitude'),
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