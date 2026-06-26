<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\DeleteAction;

class EditPermission extends EditRecordAndReturn
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
