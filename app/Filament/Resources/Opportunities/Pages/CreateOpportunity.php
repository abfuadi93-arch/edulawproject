<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Filament\Resources\Pages\CreateRecordAndReturn;

class CreateOpportunity extends CreateRecordAndReturn
{
    protected static string $resource = OpportunityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['created_by'] = $user?->id;
        $data['updated_by'] = $user?->id;

        return OpportunityResource::prepareFormDataForPersistence($data);
    }
}
