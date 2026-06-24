<?php

namespace App\Filament\Resources\ContentBlocks\Pages;

use App\Filament\Resources\ContentBlocks\ContentBlockResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use Filament\Actions\DeleteAction;

class EditContentBlock extends EditRecordAndReturn
{
    protected static string $resource = ContentBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
