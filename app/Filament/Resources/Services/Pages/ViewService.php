<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn() => ! $this->record->status->isFinal()),
            Action::make('invoice')
                ->label('Cetak Invoice')
                ->icon(Heroicon::OutlinedPrinter)
                ->visible(fn() => $this->record->status === ServiceStatus::Completed)
                ->action(fn() => response()->streamDownload(
                    fn() => print ($this->record->invoicePdf()),
                    $this->record->invoiceNumber() . '.pdf',
                )),
        ];
    }
}