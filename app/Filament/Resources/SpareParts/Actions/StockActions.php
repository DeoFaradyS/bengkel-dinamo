<?php

namespace App\Filament\Resources\SpareParts\Actions;

use App\Enums\StockMovementType;
use App\Models\SparePart;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class StockActions
{
    public static function add(): Action
    {
        return Action::make('addStock')
            ->label('Tambah Stok')
            ->icon(Heroicon::OutlinedPlusCircle)
            ->color('success')
            ->modalHeading(fn(SparePart $record) => "Tambah stok: {$record->name}")
            ->modalDescription(fn(SparePart $record) => "Stok sekarang: {$record->stock}")
            ->modalSubmitActionLabel('Simpan')
            ->schema([
                TextInput::make('quantity')->label('Jumlah masuk')
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(999999),
                Textarea::make('note')->label('Catatan')
                    ->rows(2)
                    ->maxLength(255)
                    ->placeholder('Contoh: beli dari supplier'),
            ])
            ->action(fn(SparePart $record, array $data, Action $action) => self::apply(
                $record, (int) $data['quantity'], StockMovementType::Purchase, $data['note'] ?? null, 'Stok bertambah', $action,
            ))
            ->after(fn($livewire) => self::refresh($livewire));
    }

    public static function correct(): Action
    {
        return Action::make('correctStock')
            ->label('Koreksi Stok')
            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->color('gray')
            ->modalHeading(fn(SparePart $record) => "Koreksi stok: {$record->name}")
            ->modalDescription(fn(SparePart $record) => "Stok sekarang: {$record->stock}")
            ->modalSubmitActionLabel('Simpan')
            ->schema([
                TextInput::make('quantity')->label('Selisih (+ atau -)')
                    ->required()
                    ->integer()
                    ->rules(['not_in:0'])
                    ->minValue(-999999)
                    ->maxValue(999999)
                    ->helperText('Contoh: -2 kalau 2 barang rusak atau hilang.'),
                Textarea::make('note')->label('Alasan')
                    ->required()
                    ->rows(2)
                    ->maxLength(255),
            ])
            ->action(fn(SparePart $record, array $data, Action $action) => self::apply(
                $record, (int) $data['quantity'], StockMovementType::Correction, $data['note'], 'Stok dikoreksi', $action,
            ))
            ->after(fn($livewire) => self::refresh($livewire));
    }

    private static function apply(SparePart $part, int $quantity, StockMovementType $type, ?string $note, string $title, Action $action): void
    {
        try {
            $part->addStock($quantity, $type, $note);
        } catch (\RuntimeException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
            $action->halt();

            return;
        }

        Notification::make()->title($title)->success()->send();
    }

    private static function refresh($livewire): void
    {
        if (method_exists($livewire, 'refreshFormData')) {
            $livewire->refreshFormData(['stock']);
        }
    }
}