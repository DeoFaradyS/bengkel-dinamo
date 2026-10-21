<?php

namespace App\Filament\Resources\SpareParts\RelationManagers;

use App\Enums\StockMovementType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $title = 'Riwayat stok';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d/m/Y H:i'),
                TextColumn::make('type')->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state instanceof StockMovementType ? $state->label() : $state),
                TextColumn::make('quantity')->label('Jumlah')
                    ->alignEnd()
                    ->formatStateUsing(fn($state) => ($state > 0 ? '+' : '') . $state)
                    ->color(fn($state) => $state < 0 ? 'danger' : 'success'),
                TextColumn::make('stock_after')->label('Stok setelah')
                    ->alignEnd(),
                TextColumn::make('note')->label('Catatan')
                    ->placeholder('-')
                    ->wrap(),
                TextColumn::make('user.name')->label('Oleh')
                    ->placeholder('-'),
            ])
            ->paginated([10, 25]);
    }
}