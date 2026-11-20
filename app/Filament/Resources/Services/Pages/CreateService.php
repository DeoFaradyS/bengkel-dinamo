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
    public bool $queueSkipConfirmed = false;

    public static string|Alignment $formActionsAlignment = Alignment::End;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        if ($this->queueBlockMessage()) {
            $this->mountAction('queueBlocked');
            $this->halt();
        }

        // Ada jadwal lebih awal yang belum dikerjakan: konfirmasi dulu, boleh dilewati.
        if ($this->queueAheadMessage() && !$this->queueSkipConfirmed) {
            $this->mountAction('confirmQueueSkip');
            $this->halt();
        }

        if ($this->isCompleting() && !$this->completeConfirmed) {
            $this->mountAction('confirmComplete');
            $this->halt();
        }

        $this->data['items'] = array_filter($this->data['items'] ?? [], fn($r) => filled($r['service_type_id'] ?? null));
        $this->data['serviceParts'] = array_filter($this->data['serviceParts'] ?? [], fn($r) => filled($r['spare_part_id'] ?? null));
    }

    // Status di form sedang diubah menjadi Dikerjakan?
    private function isStartingWork(): bool
    {
        $status = $this->data['status'] ?? null;
        $status = $status instanceof ServiceStatus ? $status : ServiceStatus::tryFrom((string) $status);

        if ($status !== ServiceStatus::InProgress) {
            return false;
        }

        return true;
    }

    // Penghalang: masih ada servis lain yang sedang dikerjakan (aturan 11).
    private function queueBlockMessage(): ?string
    {
        return $this->isStartingWork() ? \App\Models\Service::runningServiceMessage(null) : null;
    }

    // Peringatan: ada servis Dikonfirmasi yang jadwalnya lebih dulu.
    private function queueAheadMessage(): ?string
    {
        return $this->isStartingWork()
            ? \App\Models\Service::aheadServiceMessage(null, $this->data['scheduled_at'] ?? null)
            : null;
    }

    public function confirmQueueSkipAction(): Action
    {
        return Action::make('confirmQueueSkip')
            ->requiresConfirmation()
            ->modalHeading('Kerjakan servis ini duluan?')
            ->modalDescription(fn() => $this->queueAheadMessage() . ' Kalau pelanggan itu belum datang, servis ini boleh dikerjakan lebih dulu.')
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('warning')
            ->modalSubmitActionLabel('Ya, kerjakan')
            ->action(function () {
                $this->queueSkipConfirmed = true;
                $this->create();
            });
    }

    public function queueBlockedAction(): Action
    {
        return Action::make('queueBlocked')
            ->modalHeading('Belum bisa dikerjakan')
            ->modalDescription(fn() => $this->queueBlockMessage())
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('warning')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Mengerti');
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
        $this->data['customer_phone'] = PhoneNumber::normalize($this->data['customer_phone'] ?? null);
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
        // Servis dari panel selalu tercatat dibuat admin, jadi datanya tetap bisa diubah.
        $data['source'] = \App\Enums\ServiceSource::Admin->value;

        return \App\Models\Service::withDistance($data);
    }


}