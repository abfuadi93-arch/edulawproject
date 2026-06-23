<?php

namespace App\Filament\Resources\ProgramCategories\Pages;

use App\Filament\Resources\ProgramCategories\ProgramCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgramCategories extends ListRecords
{
    protected static string $resource = ProgramCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
