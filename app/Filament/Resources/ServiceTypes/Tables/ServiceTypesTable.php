<?php

namespace App\Filament\Resources\ServiceTypes\Tables;

use App\Models\ServiceType;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')->label('No')->rowIndex()
                    ->alignCenter()
                    ->extraHeaderAttributes(['style' => 'width: 4rem']),
                ImageColumn::make('photo')->label('Foto')
                    ->disk('public')
                    ->square()
                    ->imageSize(48),
                TextColumn::make('name')->label('Nama Jasa')->searchable()->sortable(),
                TextColumn::make('default_price')->label('Harga Acuan')
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('description')->label('Deskripsi')
                    ->limit(60)
                    ->tooltip(fn(ServiceType $record): ?string => $record->description)
                    ->placeholder('-'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}