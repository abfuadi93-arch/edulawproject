<?php

namespace App\Filament\Resources\PublicationTypes\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\PublicationTypes\PublicationTypeResource;
use Filament\Actions\DeleteAction;

class EditPublicationType extends EditRecordAndReturn
{
    protected static string $resource = PublicationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
