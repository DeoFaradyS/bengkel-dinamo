<?php

namespace App\Filament\Resources\SpareParts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                TextColumn::make('index')->label('No')->rowIndex(),
                TextColumn::make('name')->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')->label('Kategori')
                    ->searchable(),
                TextColumn::make('stock')->label('Stok')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_stock')->label('Stok Minimal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('location')->label('Lokasi')
                    ->searchable(),
                TextColumn::make('price')->label('Harga')
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->sortable(),
            ])
            ->filters([
                // TrashedFilter::make(),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->multiple()
                    ->options(fn() => \App\Models\SparePart::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category', 'category')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            // ->toolbarActions([
            //     BulkActionGroup::make([
            //         DeleteBulkAction::make(),
            //         ForceDeleteBulkAction::make(),
            //         RestoreBulkAction::make(),
            //     ]),
            // ])
        ;
    }
}
