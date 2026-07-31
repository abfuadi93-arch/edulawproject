<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Filament\Resources\Publications\PublicationResource;

class CreatePublication extends CreateRecordAndReturn
{
    protected static string $resource = PublicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['created_by'] = $user?->id;
        $data['updated_by'] = $user?->id;

        return PublicationResource::prepareFormDataForPersistence($data);
    }
}
