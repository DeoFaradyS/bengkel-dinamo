<?php

namespace App\Filament\Resources\SpareParts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SparePartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama')
                    ->required(),
                TextInput::make('category')->label('Kategori'),
                TextInput::make('stock')->label('Stok')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('min_stock')->label('Stok Minimal')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('location')->label('Lokasi'),
                TextInput::make('price')->label('Harga')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->prefix('Rp'),
            ]);
    }
}
