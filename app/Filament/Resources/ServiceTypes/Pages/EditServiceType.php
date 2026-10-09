<?php

namespace App\Filament\Resources\ServiceTypes\Pages;

use App\Filament\Resources\ServiceTypes\ServiceTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceType extends EditRecord
{
    protected static string $resource = ServiceTypeResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Simpan'),
            $this->getCancelFormAction()->label('Batal'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
