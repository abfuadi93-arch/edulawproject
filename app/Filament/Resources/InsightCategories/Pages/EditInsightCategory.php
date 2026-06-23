<?php

namespace App\Filament\Resources\InsightCategories\Pages;

use App\Filament\Resources\InsightCategories\InsightCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInsightCategory extends EditRecord
{
    protected static string $resource = InsightCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
