<?php

namespace App\Filament\Resources\Multimedia\Pages;

use App\Filament\Resources\Multimedia\MultimediaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMultimedia extends EditRecord
{
    protected static string $resource = MultimediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
