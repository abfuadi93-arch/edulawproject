<?php

namespace App\Filament\Resources\CollaborationSubmissions\Pages;

use App\Filament\Resources\CollaborationSubmissions\CollaborationSubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCollaborationSubmission extends EditRecord
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
