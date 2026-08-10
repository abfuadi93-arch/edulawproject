<?php

namespace App\Filament\Resources\Insights\InsightResource\Pages;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Services\InsightEditorialWorkflowService;
use App\Services\InsightFootnoteService;

class CreateInsight extends CreateRecordAndReturn
{
    protected static string $resource = InsightResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['created_by'] = $user?->id;
        $data['updated_by'] = $user?->id;
        $data['status'] = 'draft';
        $data = InsightResource::prepareFormDataForPersistence($data);

        $data['published_at'] = null;
        $data['reviewed_by'] = null;
        $data['reviewed_at'] = null;

        return $data;
    }

    protected function afterCreate(): void
    {
        app(InsightFootnoteService::class)->sync($this->getRecord());
        app(InsightEditorialWorkflowService::class)->recordDraftCreated($this->getRecord(), auth()->user());
    }
}
