<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Resources\Concerns\HasEdulawResourceList;
use Filament\Resources\Pages\ListRecords;

class ListAuthors extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = AuthorResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola profil penulis, penyusun, dan kontributor Edulaw.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Profil';
    }
}
