<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Filament\Resources\ProgramResource;

class CreateProgram extends CreateRecordAndReturn
{
    protected static string $resource = ProgramResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['created_by'] = $user?->id;
        $data['updated_by'] = $user?->id;
        $data['sort_order'] = ProgramResource::nextSortOrder();

        return ProgramResource::prepareFormDataForPersistence($data);
    }
}
