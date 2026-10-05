<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Filament\Resources\SpareParts\SparePartResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSparePart extends EditRecord
{
    protected static string $resource = SparePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Simpan'),
            $this->getCancelFormAction()->label('Kembali'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
