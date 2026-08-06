<?php

namespace App\Filament\Resources\Editorial\Pages;

use App\Filament\Resources\Editorial\EditorialResource;
use Filament\Resources\Pages\ListRecords;

class ListEditorialInsights extends ListRecords
{
    protected static string $resource = EditorialResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
