<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Pages\CreateRecordAndReturn;

class CreateContactMessage extends CreateRecordAndReturn
{
    protected static string $resource = ContactMessageResource::class;
}
