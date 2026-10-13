<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    public bool $completeConfirmed = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        if ($this->isCompleting() && ! $this->completeConfirmed) {
            $this->mountAction('confirmComplete');
            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        if ($this->record->status === ServiceStatus::Completed) {
            $this->record->syncStock();
        }
    }

    public function confirmCompleteAction(): Action
    {
        return Action::make('confirmComplete')
            ->requiresConfirmation()
            ->modalHeading('Selesaikan servis?')
            ->modalDescription('Stok spare part akan dikurangi dan servis tidak bisa diubah lagi.')
            ->modalSubmitActionLabel('Ya, selesaikan')
            ->action(function () {
                $this->completeConfirmed = true;
                $this->save();
            });
    }

    private function isCompleting(): bool
    {
        $status = $this->data['status'] ?? null;
        $status = $status instanceof ServiceStatus ? $status : ServiceStatus::tryFrom((string) $status);

        return $status === ServiceStatus::Completed;
    }
}