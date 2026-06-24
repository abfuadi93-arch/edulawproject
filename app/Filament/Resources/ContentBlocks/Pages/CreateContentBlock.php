<?php

namespace App\Filament\Resources\ContentBlocks\Pages;

use App\Filament\Resources\ContentBlocks\ContentBlockResource;
use App\Filament\Resources\Pages\CreateRecordAndReturn;

class CreateContentBlock extends CreateRecordAndReturn
{
    protected static string $resource = ContentBlockResource::class;
}
