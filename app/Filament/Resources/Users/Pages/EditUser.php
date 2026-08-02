<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;

class EditUser extends EditRecordAndReturn
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => UserResource::canSafelyDelete($this->getRecord())),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (UserResource::isLastActiveSuperAdmin($this->getRecord())) {
            $data['is_active'] = true;
        }

        return $data;
    }
}
