<?php

namespace App\Filament\Resources\PublicationTypes\Pages;

use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Filament\Resources\PublicationTypes\PublicationTypeResource;

class CreatePublicationType extends CreateRecordAndReturn
{
    protected static string $resource = PublicationTypeResource::class;
}
