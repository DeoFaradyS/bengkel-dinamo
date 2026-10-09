<?php

namespace App\Filament\Resources\ServiceTypes\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')->label('No')->rowIndex(),
                TextColumn::make('name')->label('Nama Jasa')->searchable()->sortable(),
                TextColumn::make('default_price')->label('Harga Acuan')
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
