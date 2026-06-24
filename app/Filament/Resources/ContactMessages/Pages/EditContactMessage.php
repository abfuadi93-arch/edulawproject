<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use Filament\Actions\DeleteAction;

class EditContactMessage extends EditRecordAndReturn
{
    protected static string $resource = ContactMessageResource::class;

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
