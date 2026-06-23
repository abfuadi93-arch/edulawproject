<?php

namespace App\Filament\Resources\InsightCategories\Pages;

use App\Filament\Resources\InsightCategories\InsightCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInsightCategories extends ListRecords
{
    protected static string $resource = InsightCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
