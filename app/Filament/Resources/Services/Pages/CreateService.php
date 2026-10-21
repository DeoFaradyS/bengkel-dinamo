<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Support\PhoneNumber;
use Filament\Support\Enums\Alignment;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;
    protected static bool $canCreateAnother = false;

    public bool $completeConfirmed = false;

    public static string|Alignment $formActionsAlignment = Alignment::End;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        if ($this->isCompleting() && !$this->completeConfirmed) {
            $this->mountAction('confirmComplete');
            $this->halt();
        }
    }

    protected function afterCreate(): void
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
                $this->create();
            });
    }

    private function isCompleting(): bool
    {
        $status = $this->data['status'] ?? null;
        $status = $status instanceof ServiceStatus ? $status : ServiceStatus::tryFrom((string) $status);

        return $status === ServiceStatus::Completed;
    }

    protected function beforeValidate(): void
    {
        $this->data['phone'] = PhoneNumber::normalize($this->data['phone'] ?? null);
    }


    protected function getFormActions(): array
{
    return [
        $this->getCreateFormAction(),
        $this->getCancelFormAction(),
    ];
}
}