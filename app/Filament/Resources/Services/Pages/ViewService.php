<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
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