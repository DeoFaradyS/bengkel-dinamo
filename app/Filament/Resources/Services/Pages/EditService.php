<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Support\PhoneNumber;
use Filament\Support\Enums\Alignment;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    public bool $completeConfirmed = false;
    public static string|Alignment $formActionsAlignment = Alignment::End;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        $this->data['items'] = array_filter($this->data['items'] ?? [], fn($r) => filled($r['service_type_id'] ?? null));
        $this->data['serviceParts'] = array_filter($this->data['serviceParts'] ?? [], fn($r) => filled($r['spare_part_id'] ?? null));
    }

    protected function afterSave(): void
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
                $this->save();
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
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;

        $type = $data['booking_type'] ?? null;
        $type = $type instanceof \App\Enums\BookingType ? $type : \App\Enums\BookingType::tryFrom((string) $type);

        $changed = $type !== $record->booking_type
            || (float) ($data['latitude'] ?? 0) !== (float) $record->latitude
            || (float) ($data['longitude'] ?? 0) !== (float) $record->longitude
            || ($type === \App\Enums\BookingType::HomeService && $record->distance_km === null);

        return $changed ? \App\Models\Service::withDistance($data) : $data;
    }

    private function statusAction(Action $action): Action
    {
        return $action
            ->record($this->record)
            ->after(function () {
                $this->record->refresh();

                // Servis jadi final (tolak/batal): keluar dari halaman Edit
                if ($this->record->status->isFinal()) {
                    $this->redirect(ServiceResource::getUrl('view', ['record' => $this->record]));

                    return;
                }

                // Servis masih aktif: segarkan form biar status tidak tertimpa saat Simpan
                $this->refreshFormData(['status', 'cancel_reason']);
            });
    }
}