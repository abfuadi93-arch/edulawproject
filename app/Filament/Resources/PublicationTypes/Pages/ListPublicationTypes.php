<?php

namespace App\Filament\Resources\PublicationTypes\Pages;

use App\Filament\Resources\PublicationTypes\PublicationTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublicationTypes extends ListRecords
{
    protected static string $resource = PublicationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
