<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Support\PhoneNumber;
use Filament\Support\Enums\Alignment;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

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

        $this->data['items'] = array_filter($this->data['items'] ?? [], fn($r) => filled($r['service_type_id'] ?? null));
        $this->data['serviceParts'] = array_filter($this->data['serviceParts'] ?? [], fn($r) => filled($r['spare_part_id'] ?? null));
    }



    protected function afterCreate(): void
    {
        if ($this->record->status === ServiceStatus::Completed) {
            try {
                $this->record->syncStock();
            } catch (\RuntimeException $e) {
                Notification::make()
                    ->danger()
                    ->title('Gagal menyelesaikan servis')
                    ->body($e->getMessage())
                    ->persistent()
                    ->send();

                throw (new Halt())->rollBackDatabaseTransaction();
            }
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return \App\Models\Service::withDistance($data);
    }


}