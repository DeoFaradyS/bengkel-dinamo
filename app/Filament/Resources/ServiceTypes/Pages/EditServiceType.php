<?php

namespace App\Filament\Resources\ServiceTypes\Pages;

use App\Filament\Resources\ServiceTypes\ServiceTypeResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

class EditServiceType extends EditRecord
{
    protected static string $resource = ServiceTypeResource::class;

    public static string|Alignment $formActionsAlignment = Alignment::End;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
{
    // "65000.00" jadi 65000, supaya mask harga di form tidak membaca titik desimal.
    $data['default_price'] = (int) round((float) $data['default_price']);

    return $data;
}
}