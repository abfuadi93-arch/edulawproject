<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Resources\Pages\CreateRecordAndReturn;

class CreateAuthor extends CreateRecordAndReturn
{
    protected static string $resource = AuthorResource::class;
}
