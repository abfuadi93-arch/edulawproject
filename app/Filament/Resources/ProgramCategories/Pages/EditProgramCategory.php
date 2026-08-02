<?php

namespace App\Filament\Resources\ProgramCategories\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\ProgramCategories\ProgramCategoryResource;
use Filament\Actions\DeleteAction;

class EditProgramCategory extends EditRecordAndReturn
{
    protected static string $resource = ProgramCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->getRecord()->programs()->exists()),
        ];
    }
}
