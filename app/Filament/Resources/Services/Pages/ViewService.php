<?php

namespace App\Filament\Resources\Services\Pages;

use App\Actions\MarkServicePaid;
use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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
            Action::make('tandai_lunas')
                ->label('Tandai Lunas')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Tandai servis ini lunas?')
                ->modalDescription('Pembayaran tunai sudah diterima. Waktu lunas tercatat sekarang.')
                ->visible(fn() => $this->record->status === ServiceStatus::Completed && $this->record->paid_at === null)
                ->action(function () {
                    $ok = app(MarkServicePaid::class)->handle($this->record);

                    // Muat ulang supaya tombol hilang dan status lunas tampil.
                    $this->record->refresh();

                    Notification::make()
                        ->title($ok ? 'Servis ditandai lunas' : 'Servis ini sudah lunas atau belum selesai')
                        ->{$ok ? 'success' : 'warning'}()
                        ->send();
                }),
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