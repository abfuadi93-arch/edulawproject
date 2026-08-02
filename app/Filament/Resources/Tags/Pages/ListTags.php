<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\ListRecords;

class ListTags extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = TagResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola tag yang digunakan pada artikel dan publikasi.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Tag';
    }
}
