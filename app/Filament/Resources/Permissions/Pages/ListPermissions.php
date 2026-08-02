<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = PermissionResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola izin teknis yang digunakan oleh role admin.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Permission';
    }
}
