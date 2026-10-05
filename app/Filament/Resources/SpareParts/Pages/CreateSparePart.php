<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Filament\Resources\SpareParts\SparePartResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSparePart extends CreateRecord
{
    protected static string $resource = SparePartResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Simpan'),
            $this->getCancelFormAction()->label('Kembali'),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
