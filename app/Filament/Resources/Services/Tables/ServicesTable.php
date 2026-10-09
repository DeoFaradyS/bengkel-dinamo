<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columnManager(false)
            ->defaultSort(fn($query) => $query
                ->orderBy('scheduled_at', 'asc')
                ->orderBy('updated_at', 'desc'))
            ->columns([
                TextColumn::make('index')->label('No')->rowIndex(),
                TextColumn::make('customer_name')->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('phone')->label('No. HP')
                    ->searchable(),
                TextColumn::make('scheduled_at')->label('Tanggal & Jam')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('status')->label('Status')
                    ->badge(),
                TextColumn::make('labor_cost')->label('Biaya Jasa')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('created_at')->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->recordActions([
                EditAction::make(),
            ])
        ;
    }
}