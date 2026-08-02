<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = UserResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola akun yang dapat mengakses panel administrasi.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Akun';
    }
}
