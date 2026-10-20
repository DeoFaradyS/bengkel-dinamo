<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')->label('No')->rowIndex()
                    ->alignCenter()
                    ->extraHeaderAttributes(['style' => 'width: 4rem']),
                TextColumn::make('name')->label('Nama Kategori')->searchable()->sortable(),
                TextColumn::make('description')->label('Deskripsi')->searchable()->sortable()->limit(50),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Ubah Kategori')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal')
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalWidth(Width::Large)
                    ->modalFooterActionsAlignment(Alignment::End),
                DeleteAction::make(),
            ]);
    }
}
