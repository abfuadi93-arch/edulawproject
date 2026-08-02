<?php

namespace App\Filament\Resources\InsightCategories\Pages;

use App\Filament\Resources\InsightCategories\InsightCategoryResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use Filament\Actions\DeleteAction;

class EditInsightCategory extends EditRecordAndReturn
{
    protected static string $resource = InsightCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->getRecord()->insights()->exists()),
        ];
    }
}
