<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\Tags\TagResource;
use Filament\Actions\DeleteAction;

class EditTag extends EditRecordAndReturn
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->getRecord()->insights()->exists() && ! $this->getRecord()->publications()->exists()),
        ];
    }
}
