<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = RoleResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola kelompok kewenangan untuk akun admin.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Role';
    }
}
