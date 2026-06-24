<?php

namespace App\Filament\Resources\CollaborationSubmissions\Pages;

use App\Filament\Resources\CollaborationSubmissions\CollaborationSubmissionResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use Filament\Actions\DeleteAction;

class EditCollaborationSubmission extends EditRecordAndReturn
{
    protected static string $resource = CollaborationSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['handled_by'] ??= auth()->id();
        $data['handled_at'] ??= now();

        return $data;
    }
}
