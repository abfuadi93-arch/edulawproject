<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Filament\Resources\Permissions\PermissionResource;

class CreatePermission extends CreateRecordAndReturn
{
    protected static string $resource = PermissionResource::class;
}
