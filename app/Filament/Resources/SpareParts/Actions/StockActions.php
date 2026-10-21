<?php

namespace App\Filament\Resources\SpareParts\Actions;

use App\Enums\StockReason;
use App\Models\SparePart;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;

class StockActions
{
    public static function change(): Action
    {
        return Action::make('changeStock')
            ->label('Ubah Stok')
            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->color('gray')
            ->modalHeading('Ubah Stok')
            ->modalSubmitActionLabel('Simpan')
            ->modalCancelActionLabel('Batal')
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalWidth(Width::Large)
            ->modalFooterActionsAlignment(Alignment::End)
            ->schema([
                ToggleButtons::make('direction')
                    ->hiddenLabel()
                    ->options(['in' => 'Tambah', 'out' => 'Kurang'])
                    ->grouped()
                    ->extraFieldWrapperAttributes(['class' => 'stock-direction'])
                    ->default('in')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('reason', null)),
                TextInput::make('quantity')->label('Jumlah')
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(999999),
                Select::make('reason')->label('Alasan')
                    ->options(fn(Get $get) => StockReason::options($get('direction') ?? 'in'))
                    ->required()
                    ->live(),
                Textarea::make('note')->label('Keterangan')
                    ->placeholder('Jelaskan alasan perubahan stok')
                    ->visible(fn(Get $get) => StockReason::tryFrom((string) $get('reason'))?->requiresNote() ?? false)
                    ->required()
                    ->rows(2)
                    ->maxLength(255),
            ])
            ->action(function (SparePart $record, array $data, Action $action) {
                $reason = StockReason::from($data['reason']);
                $quantity = $reason->isOut() ? -(int) $data['quantity'] : (int) $data['quantity'];

                try {
                    $record->addStock($quantity, $reason->type(), $data['note'] ?? null, null, $reason);
                } catch (\RuntimeException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();
                    $action->halt();

                    return;
                }

                Notification::make()
                    ->title($quantity > 0 ? 'Stok bertambah' : 'Stok berkurang')
                    ->success()
                    ->send();
            })
            ->after(function ($livewire) {
                if (method_exists($livewire, 'refreshFormData')) {
                    $livewire->refreshFormData(['stock']);
                }
            });
    }
}