<?php

namespace App\Filament\Resources\Multimedia\Pages;

use App\Filament\Resources\Multimedia\MultimediaResource;
use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Support\MultimediaThumbnail;
use Filament\Actions\Action;

class CreateMultimedia extends CreateRecordAndReturn
{
    protected static string $resource = MultimediaResource::class;

    protected function afterCreate(): void
    {
        MultimediaThumbnail::importFromSource($this->record);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Simpan dan Buat Lagi');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
