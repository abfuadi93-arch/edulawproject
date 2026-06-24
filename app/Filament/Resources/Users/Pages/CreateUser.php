<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Filament\Resources\Users\UserResource;

class CreateUser extends CreateRecordAndReturn
{
    protected static string $resource = UserResource::class;
}
