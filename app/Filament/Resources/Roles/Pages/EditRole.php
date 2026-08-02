<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;

class EditRole extends EditRecordAndReturn
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->name !== 'super_admin' && ! $this->getRecord()->users()->exists()),
        ];
    }
}
