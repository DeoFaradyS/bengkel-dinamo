<?php

namespace App\Filament\Resources\ServiceTypes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Filament\Schemas\Components\Section;

class ServiceTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')->label('Nama Jasa')
                    ->required()
                    ->trim(),
                TextInput::make('default_price')->label('Harga Acuan')
                    ->required()
                    ->numeric()
                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                    ->stripCharacters('.')
                    ->minValue(0)
                    ->maxValue(9999999999)
                    ->prefix('Rp'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
