<?php

namespace App\Filament\Resources\InsightCategories\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\InsightCategories\InsightCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListInsightCategories extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = InsightCategoryResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola klasifikasi artikel dan urutan tampil pada halaman editorial.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Kategori';
    }
}
