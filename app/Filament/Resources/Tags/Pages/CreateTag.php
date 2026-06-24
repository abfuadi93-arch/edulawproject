<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Filament\Resources\Tags\TagResource;

class CreateTag extends CreateRecordAndReturn
{
    protected static string $resource = TagResource::class;
}
