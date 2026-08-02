<?php

namespace App\Filament\Resources\PublicationTypes\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\PublicationTypes\PublicationTypeResource;
use Filament\Resources\Pages\ListRecords;

class ListPublicationTypes extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = PublicationTypeResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola klasifikasi riset dan publikasi.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Tipe Publikasi';
    }
}
