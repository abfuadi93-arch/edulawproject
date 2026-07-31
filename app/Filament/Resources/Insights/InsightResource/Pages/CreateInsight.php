<?php

namespace App\Filament\Resources\Insights\InsightResource\Pages;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Pages\CreateRecordAndReturn;

class CreateInsight extends CreateRecordAndReturn
{
    protected static string $resource = InsightResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['created_by'] = $user?->id;
        $data['updated_by'] = $user?->id;
        $data = InsightResource::prepareFormDataForPersistence($data);

        if (! InsightResource::canManageEditorialWorkflow()) {
            $data['status'] = 'draft';
            $data['published_at'] = null;
            $data['featured'] = false;
            $data['editor_pick'] = false;
            $data['sort_order'] = 0;
            $data['reviewed_by'] = null;
            $data['reviewed_at'] = null;
        }

        return $data;
    }
}
