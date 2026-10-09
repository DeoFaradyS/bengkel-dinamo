<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ServiceStatus;
use App\Models\ServiceType;
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
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Validation\Rules\Unique;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pelanggan')
                    ->columnSpanFull()
                    ->columns(2)
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
                    ->columnSpanFull()
                    ->columns(2)
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
                            ->inline()
                            ->required(),
                        Textarea::make('complaint')->label('Keluhan')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Jasa')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->hiddenLabel()
                            ->addActionLabel('Tambah jasa')
                            ->live()
                            ->table([
                                TableColumn::make('Jasa'),
                                TableColumn::make('Harga'),
                            ])
                            ->schema([
                                Select::make('service_type_id')
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
            ]);
    }
}