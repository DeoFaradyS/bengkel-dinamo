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
}