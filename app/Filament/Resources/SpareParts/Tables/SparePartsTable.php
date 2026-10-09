<?php

namespace App\Filament\Resources\SpareParts\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

class SparePartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(fn($query) => $query
                ->orderByRaw('stock <= min_stock desc')
                ->orderByRaw('case when stock <= min_stock then min_stock - stock end desc')
                ->orderBy('updated_at', 'desc'))

            ->recordClasses(fn($record) => $record->stock <= $record->min_stock
                ? 'bg-red-50!'
                : null)
            ->columns([
                ImageColumn::make('photo')->label('Foto')
                    ->disk('public')
                    ->square(),
                TextColumn::make('code')->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')->label('Kategori')
                    ->placeholder('Tanpa kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stock')->label('Stok')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('location')->label('Lokasi')
                    ->searchable(),
                TextColumn::make('price')->label('Harga')
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
