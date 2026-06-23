<?php

namespace App\Filament\Resources\Multimedia\Pages;

use App\Filament\Resources\Multimedia\MultimediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMultimedia extends ListRecords
{
    protected static string $resource = MultimediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
