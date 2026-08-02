<?php

namespace App\Filament\Resources\ProgramCategories\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\ProgramCategories\ProgramCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListProgramCategories extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = ProgramCategoryResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola klasifikasi program dan urutan tampil.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Kategori';
    }
}
